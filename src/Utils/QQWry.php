<?php

namespace App\Utils;

use PDO;

class QQWry
{
    private $fp;
    private $firstip;
    private $lastip;
    private $totalip;
    private $db;

    public function __construct()
    {
        $filename = BASE_PATH . '/storage/qqwry.dat';
        $dbFilename = BASE_PATH . '/storage/country_asn.db';

        if (($this->fp = fopen($filename, 'rb')) !== false) {
            $this->firstip = $this->getlong();
            $this->lastip = $this->getlong();
            $this->totalip = ($this->lastip - $this->firstip) / 7;
            register_shutdown_function(array(&$this, '__destruct'));
        }

        // 连接SQLite数据库
        $this->db = new PDO('sqlite:' . $dbFilename);
    }

    public function __destruct()
    {
        if ($this->fp) {
            fclose($this->fp);
        }
        $this->fp = 0;
    }

    private function getlong()
    {
        $result = unpack('Vlong', fread($this->fp, 4));
        return $result['long'];
    }

    private function getlong3()
    {
        $result = unpack('Vlong', fread($this->fp, 3) . chr(0));
        return $result['long'];
    }

    private function packip($ip)
    {
        return pack('N', (int)ip2long($ip));
    }

    private function getstring($data = '')
    {
        $char = fread($this->fp, 1);
        while (ord($char) > 0) {
            $data .= $char;
            $char = fread($this->fp, 1);
        }
        return $data;
    }

    private function getarea()
    {
        $byte = fread($this->fp, 1);
        switch (ord($byte)) {
            case 0:
                $area = '';
                break;
            case 1:
            case 2:
                fseek($this->fp, $this->getlong3());
                $area = $this->getstring();
                break;
            default:
                $area = $this->getstring($byte);
                break;
        }
        return $area;
    }

    private function isIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    private function inet_pton_hex($ip)
    {
        $packed = inet_pton($ip);
        $hex = unpack('H*', $packed);
        return reset($hex);
    }

    private function findIPv6Location($ip)
    {
        $ipHex = $this->inet_pton_hex($ip);

        $stmt = $this->db->prepare('SELECT country_name, as_name, as_domain FROM country_asn WHERE :ip BETWEEN start_ip_hex AND end_ip_hex');
        $stmt->bindParam(':ip', $ipHex);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $result['country'] = $result['country_name'] . ', ';
            $result['area'] = $result['as_name'] . ', ' . $result['as_domain'];
        }

        return $result;
    }

    public function getlocation($ip)
    {
        if ($this->isIPv6($ip)) {
            return $this->findIPv6Location($ip);
        }

        if (!$this->fp) {
            return null;
        }

        $location['ip'] = gethostbyname($ip);
        $ip = $this->packip($location['ip']);

        $l = 0;
        $u = $this->totalip;
        $findip = $this->lastip;

        while ($l <= $u) {
            $i = floor(($l + $u) / 2);
            fseek($this->fp, $this->firstip + $i * 7);
            $beginip = strrev(fread($this->fp, 4));

            if ($ip < $beginip) {
                $u = $i - 1;
            } else {
                fseek($this->fp, $this->getlong3());
                $endip = strrev(fread($this->fp, 4));

                if ($ip > $endip) {
                    $l = $i + 1;
                } else {
                    $findip = $this->firstip + $i * 7;
                    break;
                }
            }
        }

        fseek($this->fp, $findip);
        $location['beginip'] = long2ip($this->getlong());
        $offset = $this->getlong3();
        fseek($this->fp, $offset);
        $location['endip'] = long2ip($this->getlong());
        $byte = fread($this->fp, 1);

        switch (ord($byte)) {
            case 1:
                $countryOffset = $this->getlong3();
                fseek($this->fp, $countryOffset);
                $byte = fread($this->fp, 1);
                switch (ord($byte)) {
                    case 2:
                        fseek($this->fp, $this->getlong3());
                        $location['country'] = $this->getstring();
                        fseek($this->fp, $countryOffset + 4);
                        $location['area'] = $this->getarea();
                        break;
                    default:
                        $location['country'] = $this->getstring($byte);
                        $location['area'] = $this->getarea();
                        break;
                }
                break;
            case 2:
                fseek($this->fp, $this->getlong3());
                $location['country'] = $this->getstring();
                fseek($this->fp, $offset + 8);
                $location['area'] = $this->getarea();
                break;
            default:
                $location['country'] = $this->getstring($byte);
                $location['area'] = $this->getarea();
                break;
        }

        if ($location['country'] == ' CZ88.NET') {
            $location['country'] = '未知';
        }

        if ($location['area'] == ' CZ88.NET') {
            $location['area'] = '';
        }

        return $location;
    }
}
