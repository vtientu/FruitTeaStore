<?php
/** SMTP with verified TLS. Configuration and credentials belong to this project only. */
function send_order_smtp(array $config, string $recipient, string $subject, string $body): void
{
    foreach ([$recipient, $config['from_address']] as $address) {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $address)) {
            throw new RuntimeException('Invalid mail address');
        }
    }
    if (!in_array($config['encryption'], ['tls', 'ssl'], true) ||
        !preg_match('/^[a-zA-Z0-9.-]+$/D', $config['host'])) {
        throw new RuntimeException('Invalid SMTP configuration');
    }
    $socket = stream_socket_client(
        ($config['encryption'] === 'ssl' ? 'ssl://' : 'tcp://') . $config['host'] . ':' . (int) $config['port'],
        $errno, $error, 10, STREAM_CLIENT_CONNECT,
        stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $config['host']]]),
    );
    if (!$socket) {
        throw new RuntimeException('SMTP connection failed');
    }
    stream_set_timeout($socket, 10);
    $read = static function (array $expected) use ($socket): void {
        do {
            $line = fgets($socket, 4096);
            if ($line === false || !preg_match('/^\d{3}[ -]/', $line)) {
                throw new RuntimeException('SMTP response unavailable');
            }
        } while ($line[3] === '-');
        if (!in_array((int) substr($line, 0, 3), $expected, true)) {
            // Do not expose SMTP responses: they can contain credentials or customer data.
            throw new RuntimeException('SMTP rejected request: ' . substr($line, 0, 3));
        }
    };
    $write = static function (string $value) use ($socket): void {
        while ($value !== '') {
            $count = fwrite($socket, $value);
            if (!$count) {
                throw new RuntimeException('SMTP write failed');
            }
            $value = substr($value, $count);
        }
    };
    $command = static function (string $value, array $expected) use ($write, $read): void {
        $write($value . "\r\n");
        $read($expected);
    };
    try {
        $read([220]);
        $command('EHLO localhost', [250]);
        if ($config['encryption'] === 'tls') {
            $command('STARTTLS', [220]);
            if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                throw new RuntimeException('SMTP TLS failed');
            }
            $command('EHLO localhost', [250]);
        }
        $command('AUTH LOGIN', [334]);
        $command(base64_encode($config['username']), [334]);
        $command(base64_encode($config['password']), [235]);
        $command('MAIL FROM:<' . $config['from_address'] . '>', [250]);
        $command('RCPT TO:<' . $recipient . '>', [250, 251]);
        $command('DATA', [354]);
        $encoded = static fn(string $value): string => '=?UTF-8?B?' . base64_encode($value) . '?=';
        $message = 'Date: ' . date(DATE_RFC2822) . "\r\n" .
            'From: ' . $encoded($config['from_name']) . ' <' . $config['from_address'] . ">\r\n" .
            'To: <' . $recipient . ">\r\n" .
            'Subject: ' . $encoded($subject) . "\r\n" .
            "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" .
            chunk_split(base64_encode($body), 76, "\r\n");
        $write($message . ".\r\n");
        $read([250]);
        // Once DATA is accepted, a disconnect during QUIT must not mark delivery as failed.
        @fwrite($socket, "QUIT\r\n");
    } finally {
        fclose($socket);
    }
}
