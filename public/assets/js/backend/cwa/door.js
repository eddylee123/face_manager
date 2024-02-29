define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cwa/door/index' + location.search,
                    table: 'door_index',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: '打卡时间',
                sortName: '打卡时间',
                sortOrder: 'desc',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    // ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [

                        {field: '工号', title: __('工号'), operate: 'like'},
                        {field: '姓名', title: __('姓名'), operate: 'like'},
                        {field: '打卡时间', title: __('打卡时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, sortable:true},
                        {field: '位置ID', title: __('安装位置'), searchList: Config.kqLevel, formatter: Table.api.formatter.normal},
                        {field: '安装地址', title: __('设备名'), operate: false},
                        {field: '进出标识', title: __('进出标识'), operate: false},
                        {field: '联系电话', title: __('联系电话'), operate: false},
                        {field: '部门名称', title: __('部门名称'), operate: false},
                        {field: '级别', title: __('级别'), operate: false},
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
        emp: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cwa/door/emp' + location.search,
                    table: 'door_index',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: '打卡时间',
                sortName: '打卡时间',
                sortOrder: 'desc',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    // ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        {field: '工号', title: __('工号'), operate: false},
                        {field: '姓名', title: __('姓名'), operate: false},
                        {field: '打卡时间', title: __('打卡时间'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, sortable:true},
                        {field: '位置ID', title: __('安装位置'), searchList: Config.kqLevel, formatter: Table.api.formatter.normal},
                        {field: '安装地址', title: __('设备名'), operate: false},
                        {field: '进出标识', title: __('进出标识'), operate: false},
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
