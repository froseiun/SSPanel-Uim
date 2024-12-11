{$load=$point_node->getNodeLoad()}

<div class="ui-card-wrap">
    <div class="row">
        <!-- Loadavg 部分 -->
        <div class="col-xx-12 col-sm-6">
            <div class="card">
                <div class="card-main">
                    <div class="card-inner">
                        <p>Loadavg {$prefix}</p>
                        <ul>
                            {foreach $load as $single_load}
                            <li>时间: {date('Y-m-d H:i:s', $single_load->log_time)}, Load: {$single_load->getNodeLoad()}</li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 节点在线情况部分 -->
        <div class="col-xx-12 col-sm-6">
            <div class="card">
                <div class="card-main">
                    <div class="card-inner">
                        <p>最近一天节点在线情况 {$prefix} - 在线 {$point_node->getNodeUptime()}</p>
                        <ul>
                            <li>在线率: {number_format($point_node->getNodeUpRate()*100,2)}%</li>
                            <li>离线率: {number_format((1-$point_node->getNodeUpRate())*100,2)}%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 节点在线人数情况部分 -->
        <div class="col-xx-12 col-sm-6">
            <div class="card">
                <div class="card-main">
                    <div class="card-inner">
                        <p>最近一天节点在线人数情况 {$prefix}</p>
                        <ul>
                            {foreach $load as $single_load}
                            <li>时间: {date('Y-m-d H:i:s', $single_load->log_time)}, 在线人数: {$single_load->online_user}</li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
