define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'emp_role/index' + location.search,
                    table: 'emp_role',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                commonSearch: false,//快速搜索
                visible: false,//浏览模式(卡片切换)、显示隐藏列、导出、通用搜索全部隐藏
                showToggle: false,//浏览模式可以切换卡片视图和表格视图两种模式
                showColumns: false,//列，可隐藏不显示的字段
                search:false,//快速搜索，搜索框
                showExport: false,//导出

                columns: [
                    [
                        {field: 'id', title: __('序号'), operate: false},
                        // {field: 'emp_id_2', title: __('Emp_id_2'), operate: false},
                        {field: 'create_time', title: __('操作时间'), addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime, operate: false},
                        {field: 'remark', title: __('描述'), operate: false},
                        {field: 'cs_text', title: __('食堂权限'), operate: false},
                        {field: 'kq_text', title: __('门禁权限'), operate: false},
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('#ctime').attr('autocomplete','off');
                }
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        lists: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'emp_role/lists' + location.search,
                    table: 'role_lists',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        {field: 'id', title: __('序号'), operate: false},
                        // {field: 'emp_id_2', title: __('Emp_id_2'), operate: false},
                        {field: 'create_time', title: __('操作时间'), addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime, operate: 'range'},
                        {field: 'remark', title: __('描述'), operate: false},
                        {field: 'cs_text', title: __('食堂权限'), operate: false},
                        {field: 'kq_text', title: __('门禁权限'), operate: false},
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
