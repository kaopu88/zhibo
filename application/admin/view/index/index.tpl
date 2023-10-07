<extend name="public:base_nav2"/>
<block name="css">
    <link rel="stylesheet" type="text/css" href="__CSS__/index/index.css?v=__RV__"/>
    <style>
        :root{
            font-size:16px !important;
        }
    </style>
</block>
<block name="js">
    <script src="__VENDOR__/echarts/echarts.min.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/shine.js?v=__RV__"></script>
    <script src="__VENDOR__/echarts/dataTool.js?v=__RV__"></script>
    <script src="__JS__/index/index.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="toggle_container">
        <include file="public/toggle"/>
    </div>
    <div class="index_main">
        <div class="welcome_message">
            欢迎访问{:APP_PREFIX_NAME}ERP管理系统！
			<!--<a style="float: right;" href="">进入个人中心</a>-->        </div>
        <div class="panel-body" style="overflow: hidden;overflow-x: auto;">
            <div class="functions">
                <div class="">
                    <a href="{:url('user/index')}">
                        <div class="fun_icon"><span class="icon-users"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$userNum}</div>
                        <div class="stat_name">用户</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('agent/index')}">
                        <div class="fun_icon"><span class="icon-home"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$agentNum}</div>
                        <div class="stat_name">{:config('app.agent_setting.agent_name')}</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('anchor/index')}">
                        <div class="fun_icon"><span class="icon-user"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$anchorNum}</div>
                        <div class="stat_name">主播</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('promoter/index')}">
                        <div class="fun_icon"><span class="icon-user"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$promoterNum}</div>
                        <div class="stat_name">{:config('app.agent_setting.promoter_name')}</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('live/index')}">
                        <div class="fun_icon"><span class="icon-film"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$liveNum}</div>
                        <div class="stat_name">直播</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('article/index')}">
                        <div class="fun_icon"><span class="icon-pencil"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$articleNum}</div>
                        <div class="stat_name">文章</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('help/index')}">
                        <div class="fun_icon"><span class="icon-help"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$helpNum}</div>
                        <div class="stat_name">帮助中心</div>
                    </div>
                </div>
                <div class="">
                    <a href="{:url('packages/index')}">
                        <div class="fun_icon"><span class="icon-settings"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num">{$settingNum}</div>
                        <div class="stat_name">总安装量</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data_block_container">
            <div class="data_block mt_10 recharge_trend">
                <div class="data_title">充值趋势图 单位(元)</div>
                <div class="data_toolbar">
                    <div class="data_date">
                        <div class="data_date_line">
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                        </div>
                        <input class="data_date_input" readonly/>
                        <input type="hidden" class="data_date_unit"/>
                        <input type="hidden" class="data_date_start"/>
                        <input type="hidden" class="data_date_end"/>
                    </div>
                </div>
                <div style="width: 100%;height:350px;" class="mt_10 my_container">
                </div>
            </div>

            <div class="data_block mt_10 consume_trend">
                <div class="data_title">消费趋势图 单位({:config('app.product_info.bean_name')})</div>
                <div class="data_toolbar">
                    <div class="data_date">
                        <div class="data_date_line">
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                        </div>
                        <input class="data_date_input" readonly/>
                        <input type="hidden" class="data_date_unit"/>
                        <input type="hidden" class="data_date_start"/>
                        <input type="hidden" class="data_date_end"/>
                    </div>
                </div>
                <div style="width: 100%;height:350px;" class="mt_10 my_container">
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class= "brand-card-container">
                <div class="brand-card-items">
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('video/index')}">短视频统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$filmNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$filmCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('video/audit_list', ['audit_status' => 1])}">{$filmSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('user_verified/index')}">评论统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$commentNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">0</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red">0</div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('user_data_deal/index')}">用户资料统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$userDataNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$userDataCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('user_data_deal/check', ['audit_status' => 0])}">{$userDataSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('complaint/index')}">举报统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$complaintNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$complaintCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('complaint/check', ['audit_status' => 0])}">{$complaintSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="brand-card-items">
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('viewback/index')}">反馈统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$viewbackNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$viewbackCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('viewback/check', ['audit_status' => 0])}">{$viewbackSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('creation/index')}">创作号统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$creationNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$creationCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('creation/check', ['audit_status' => 0])}">{$creationSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('recharge_app/all_list')}">充值统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$rechargeAppNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$rechargeAppCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('recharge_app/index', ['audit_status' => 0])}">{$rechargeAppSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="{:url('user_verified/index')}">实名认证统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value">{$userVerifiedNum}</div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">{$userVerifiedCheckNum}</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="{:url('user_verified/audit', ['status' => 0])}">{$userVerifiedSelfCheckNum}</a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <notempty name="admin_notice">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td>最新公告</td>
                    <td>发布人</td>
                    <td>时间</td>
                </tr>
                </thead>
               <tbody>
                <volist name="admin_notice" id="an">
               <tr>
                   <td>
                       <a href="{:url('notice/detail',['id'=>$an['id']])}" target="_blank">{$an.title}</a>
                   </td>
                   <td>{$an.username}</td>
                   <td>{$an.create_time|time_format}</td>
               </tr>
               </volist>
               </tbody>
            </table>
            </notempty>
            <div class="panel mt_10">
                <div class="panel-heading">服务器状态</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td>运行状态</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>直播在线人数</td>
                            <td>***</td>
                        </tr>
                        <tr>
                            <td>环境检测</td>
                            <td>
                                production：production
                            </td>
                        </tr>
                        <tr>
                            <td>node服务</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>数据库</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>系统版本</td>
                            <td>3.0.0</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div style="height: 30px"></div>
        </div>
    </div>
</block>