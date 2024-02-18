define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'exam_log/index' + location.search,
                    // add_url: 'exam_log/add',
                    // edit_url: 'exam_log/edit',
                    // del_url: 'exam_log/del',
                    // multi_url: 'exam_log/multi',
                    // import_url: 'exam_log/import',
                    table: 'exam_log',
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
                    // ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'id_card', title: __('Id_card'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'),searchList: Config.status_list, formatter: Table.api.formatter.status},
                        {field: 'emp_status', title: __('Emp_status'),searchList: Config.status_emp, formatter: Table.api.formatter.status, operate:false},
                        {field: 'remark', title: __('Remark'), operate:false},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime}
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
        lists: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'exam_log/lists' + location.search,
                    table: 'exam_err',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                // searchFormVisible:true,
                commonSearch: false,//快速搜索
                visible: false,//浏览模式(卡片切换)、显示隐藏列、导出、通用搜索全部隐藏
                showToggle: false,//浏览模式可以切换卡片视图和表格视图两种模式
                showColumns: false,//列，可隐藏不显示的字段
                search:false,//快速搜索，搜索框
                showExport: false,//导出

                columns: [
                    [
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'id_card', title: __('Id_card'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'),searchList: Config.status_list, formatter: Table.api.formatter.status},
                        {field: 'remark', title: __('Remark'), operate:false},
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
