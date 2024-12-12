<?php

namespace App\Models;

use App\Utils\Tools;

class TrafficLog extends Model
{
    protected $connection = 'default';
    protected $table = 'user_traffic_log';

    public static function dataUnitConvert(string $data): string
    {
        if ($data < 1024) {
            return number_format($data, 2, ".", "") . " B";
        } elseif ($data < 1024 * 1024) {
            return number_format($data / 1024, 2, ".", "") . " KB";
        } else {
            return number_format($data / (1024 * 1024), 2, ".", "") . " MB";
        }
    }

    public function node()
    {
        $node = Node::where('id', $this->attributes['node_id'])->first();
        if ($node == null) {
            self::where('id', '=', $this->attributes['id'])->delete();
            return null;
        }

        return $node;
    }

    public function user()
    {
        $user = User::where('id', $this->attributes['user_id'])->first();
        if ($user == null) {
            self::where('id', '=', $this->attributes['id'])->delete();
            return null;
        }

        return $user;
    }

    public function totalUsed()
    {
        return Tools::flowAutoShow($this->attributes['u'] + $this->attributes['d']);
    }

    public function totalUsedRaw()
    {
        return number_format(($this->attributes['u'] + $this->attributes['d']) / 1024, 2, '.', '');
    }

    public function logTime()
    {
        return Tools::toDateTime($this->attributes['log_time']);
    }

    public function nodecolor()
    {
        $hash = md5((string)$this->attributes['node_id']);
        return '#' . substr($hash, 0, 6);
    }

    public function downloadtraffic()
    {
        $rawtraffic = $this->attributes['d'];
        return $this->dataUnitConvert($rawtraffic);
    }

    public function uploadtraffic()
    {
        $rawtraffic = $this->attributes['u'];
        return $this->dataUnitConvert($rawtraffic);
    }
}
