define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'role_status/index' + location.search,
                    add_url: 'role_status/add',
                    edit_url: 'role_status/edit',
                    del_url: 'role_status/del',
                    multi_url: 'role_status/multi',
                    import_url: 'role_status/import',
                    table: 'role_status',
                }
            });

            var table = $("#table");

            //  e.preventDefault();
            //var ids = Table.api.selectedids(table);
            /*
            Config.columns
            Config.moduleurl
            Config.controllername
               , formatter: function(val, row){
               var html = url_class_val(row.id+'" datas="status=1' , "btn btn-xs btn-success btn-editone" , '<i class="fa fa-pencil"></i>');
               return html;
               }}
               */
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
                        {checkbox: true},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'operate', title: __('Operate'), operate: 'LIKE', searchList: Config.view_list, formatter: Table.api.formatter.normal},
                        {field: 'emp_source', title: __('Emp_source'), searchList: Config.source_list, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: Config.status_list, formatter: Table.api.formatter.normal, operate: false},
                        {field: 'org_id', title: __('Org_id'), searchList: Config.org_list, formatter: Table.api.formatter.normal},
                        {field: 'to_page', title: __('To_page'), searchList: Config.page_list, formatter: Table.api.formatter.normal, operate: false},
                        {field: 'url', title: __('Url'), operate: false},
                        {field: 'params', title: __('Params'), operate: false},
                        {field: 'create_time', title: __('Create_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime}
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
