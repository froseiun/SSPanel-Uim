{include file='user/main.tpl'}

<main class="content">
    <div class="content-header ui-content-header">
        <div class="container">
            <h1 class="content-heading">流量图表 (InfluxDB)</h1>
        </div>
    </div>
    <div class="container">
        <section class="content-inner margin-top-no">
            <div class="ui-card-wrap">
                <div class="row">
                    <div class="col-lg-12 col-sm-12">
                        <div class="card">
                            <div class="card-main">
                                <div class="card-inner">
                                    <p>注意：部分节点实际上传流量和实际下载流量相反。</p>
                                    <p>此处实际流量与您运营商计算方式不同，可能会略微小于运营商的计费流量，仅供参考。</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-sm-12">
                        <div class="card">
                            <div class="card-main">
                                <div class="card-inner">
                                    <div id="log_chart" style="height: 300px; width: 100%;"></div>
                                    <script src="/assets/js/canvasjs.min.js"></script>
                                    <script type="text/javascript">
                                        window.onload = function () {
                                            var log_chart = new CanvasJS.Chart("log_chart",
                                                {
                                                    zoomEnabled: true,
                                                    title: {
                                                        text: "最近流量消耗",
                                                        fontSize: 20
                                                    },
                                                    animationEnabled: true,
                                                    axisX: {
                                                        title: "时间",
                                                        labelFontSize: 14,
                                                        titleFontSize: 18
                                                    },
                                                    axisY: {
                                                        title: "实际流量/MB",
                                                        lineThickness: 2,
                                                        labelFontSize: 14,
                                                        titleFontSize: 18
                                                    },
                                                    data: [
                                                        {foreach from=$data key=node_id item=node}
                                                        {
                                                            type: "scatter",
                                                            name: {$node_id},
                                                            {literal}
                                                            toolTipContent: "" +
                                                                "<span style=color: {color};><strong>产生时间: </strong></span>" +
                                                                "{x} <br/>" +
                                                                "<span style=color: {color};><strong>计费流量: </strong></span>" +
                                                                "{traffic} <br/>" +
                                                                "<span style=color: {color};><strong>实际上传: </strong></span>" +
                                                                "{upload} <br/><span style=color: {color};><strong>实际下载: </strong></span>" +
                                                                "{download} <br/>" +
                                                                "<span style=color: {color};><strong>产生节点: </strong></span>" +
                                                                "{node_name}",
                                                            {/literal}
                                                            dataPoints: [
                                                                {foreach $node as $single_log}
                                                                {
                                                                    x: new Date("{$single_log["_time"]}"),
                                                                    y: {($single_log["d"] + $single_log["u"])/(1024 * 1024)},
                                                                    node_name: "{$single_log["node_name"]}",
                                                                    traffic: "{App\Models\TrafficLog::dataUnitConvert($single_log["traffic_metered"])}",
                                                                    download: "{App\Models\TrafficLog::dataUnitConvert($single_log["d"])}",
                                                                    upload: "{App\Models\TrafficLog::dataUnitConvert($single_log["u"])}"
                                                                },
                                                                {/foreach}
                                                            ]
                                                        },
                                                        {/foreach}

                                                    ]
                                                });
                                            log_chart.render();
                                        }
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

{include file='user/footer.tpl'}
