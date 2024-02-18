define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cwa/cater_b/index' + location.search,
                    table: 'cater_index',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: '日期',
                sortName: '日期',
                sortOrder: 'desc',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        // {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons:
                                [
                                    {
                                        name: 'read',
                                        text:__('查看'),
                                        title: __('查看'),
                                        //图标
                                        icon: 'fa fa-external-link',
                                        //btn-dialog表示为弹窗
                                        classname: 'btn btn-xs btn-info btn-dialog',
                                        //弹窗大小
                                        extend: 'data-area=\'["100%","100%"]\'',
                                        url: 'cwa/cater_b/detail?date={row.日期}&area={row.食堂}',
                                    },
                                ], operate:false, formatter: Table.api.formatter.buttons
                        },
                        {field: '日期', title: __('日期'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: '星期', title: __('星期'), searchList: Config.weekList, formatter: Table.api.formatter.normal},
                        {field: '食堂', title: __('食堂'), operate: false},
                        {field: '消费总数', title: __('消费总数'), operate: false},
                        {field: '总数早餐', title: __('总数早餐'), operate: false},
                        {field: '总数午餐', title: __('总数午餐'), operate: false},
                        {field: '总数晚餐', title: __('总数晚餐'), operate: false},
                        {field: '总数宵夜', title: __('总数宵夜'), operate: false},
                        {field: '总数其他', title: __('总数其他'), operate: false},
                        {field: '一线早餐', title: __('一线早餐'), operate: false},
                        {field: '一线午餐', title: __('一线午餐'), operate: false},
                        {field: '一线晚餐', title: __('一线晚餐'), operate: false},
                        {field: '一线宵夜', title: __('一线宵夜'), operate: false},
                        {field: '一线其他', title: __('一线其他'), operate: false},
                        {field: '行管早餐', title: __('行管早餐'), operate: false},
                        {field: '行管午餐', title: __('行管午餐'), operate: false},
                        {field: '行管晚餐', title: __('行管晚餐'), operate: false},
                        {field: '行管宵夜', title: __('行管宵夜'), operate: false},
                        {field: '行管其他', title: __('行管其他'), operate: false},
                        {field: '其他早餐', title: __('其他早餐'), operate: false},
                        {field: '其他午餐', title: __('其他午餐'), operate: false},
                        {field: '其他晚餐', title: __('其他晚餐'), operate: false},
                        {field: '其他宵夜', title: __('其他宵夜'), operate: false},
                        {field: '其他', title: __('其他'), operate: false},
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('.search , .columns-right').hide();
                    $('.btn_export').unbind('click').click(function(){
                        $('.dropdown-menu li[data-type="excel"]').trigger('click');
                    });
                    $('#ctime').attr('autocomplete','off');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cwa/cater_b/detail' + location.search,
                    table: 'cater_detail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                // pk: '日期',
                // sortName: '日期',
                // sortOrder: 'desc',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    // ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        {field: '打卡日期', title: __('打卡日期'), operate: false, addclass:'datetimerange', autocomplete:false},
                        {field: '打卡时间', title: __('打卡时间'), operate: false},
                        {field: '工号', title: __('工号'), operate: 'like'},
                        {field: '姓名', title: __('姓名'), operate: 'like'},
                        {field: '部门', title: __('部门'), operate: false},
                        {field: '餐别', title: __('餐别'), operate: false},
                        {field: '机器号', title: __('机器号'), operate: false},
                        {field: '卡号', title: __('卡号'), operate: false},
                        {field: '消费级别', title: __('消费级别'), operate: false},
                        {field: '门禁级别', title: __('门禁级别'), operate: false},
                        {field: '食堂', title: __('食堂'), operate: false},
                        {field: '消费模式', title: __('消费模式'), operate: false},
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('.search , .columns-right').hide();
                    $('.btn_export').unbind('click').click(function(){
                        $('.dropdown-menu li[data-type="excel"]').trigger('click');
                    });
                    $('#ctime').attr('autocomplete','off');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
