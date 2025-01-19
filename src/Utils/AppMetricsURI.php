<?php

namespace App\Utils;

class AppMetricsURI
{
    private static function formatNodeID($id)
    {
        $id_str = sprintf("%03d", $id); // keep leading 0s
        return $id_str;
    }

    private static function formatBlackboxModuleName($id)
    {
        $id_str = self::formatNodeID($id);
        return "tcp_{$id_str}";
    }

    public static function getBlackboxAlloyURI($user)
    {
        return <<<EOD
logging {
  level = "info"
}

prometheus.scrape "kyaru_server" {
  targets    = prometheus.exporter.blackbox.kyaru_blackbox.targets
  forward_to = [prometheus.remote_write.kyaru_server.receiver]
  scrape_interval = "120s"
}

prometheus.remote_write "kyaru_server" {
  endpoint {
    url = "{$_ENV['baseUrl']}/prometheus/api/v1/write"

    basic_auth {
      username = "usr{$user['id']}"
      password = "{$user['uuid']}"
    }
  }
}

discovery.file "kyaru_blackbox" {
  files = ["/etc/alloy/targets.yaml"]
}

prometheus.exporter.blackbox "kyaru_blackbox" {
  config_file = "/etc/alloy/modules.yaml"
  targets = discovery.file.kyaru_blackbox.targets
}
EOD;

    }

    public static function getBlackboxTargetsURI(array $item, int $node_id, $user)
    {
        $address = $item['address'] ?? $item['add']; // normal / vmess
        return [
            'targets' => ["{$address}:{$item['port']}"],
            'labels' => [
                'name' => self::formatNodeID($node_id),
                'module' => self::formatBlackboxModuleName($node_id),

                // custom fields
                'server_name' => $item['remark'],
                'instance' => $user['user_name'],
                'gyokuro' => 'kyaru'  // easier filtering on Grafana
            ]
        ];

    }

    public static function getBlackboxModulesURI(array $item, int $node_id)
    {
        return [
            self::formatBlackboxModuleName($node_id) => [
                'prober' => 'tcp',
                'timeout' => '5s',
                'tcp' => [
                    'tls' => true,
                    'tls_config' => [
                        'insecure_skip_verify' => true,
                        'server_name' => $item['host'] ?? $item['address'] ?? ''
                    ]
                ]
            ]
        ];
    }
}
