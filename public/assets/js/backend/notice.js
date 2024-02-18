define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'notice/index' + location.search,
                    // add_url: 'notice/add',
                    // // edit_url: 'notice/edit',
                    // // del_url: 'notice/del',
                    multi_url: 'notice/multi',
                    // import_url: 'notice/import',
                    table: 'notice',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'is_read',
                sortOrder: 'asc',
                searchFormVisible:true,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        {checkbox: true},
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
                                        classname: 'btn btn-xs btn-info',
                                        //弹窗大小
                                        extend:' target="_blank"',
                                        url: function (row){
                                            return "/admin123.php/employee/detail?from=notice&ids="+row.link_id;
                                        }
                                    },
                                ], operate:false, formatter: Table.api.formatter.buttons
                        },
                        {field: 'type', title: __('Type'),visible:false,searchList: Config.typeList, formatter: Table.api.formatter.status},
                        {field: 'title', title: __('Title'), operate: false},
                        {field: 'content', title: __('Content'), operate: false},
                        {field: 'is_read', title: __('Is_read'),searchList: Config.readList, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('Create_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'nickname', title: __('操作人'), operate: false},
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
            $(document).on("click", ".btn-read", function () {
                var ids = Table.api.selectedids(table);//获取选中列的id
                if(ids.length==0){
                    Toastr.error("最少选择一条记录操作");
                    return false;
                }
                $.post(base_file+"/notice/read", {nid:ids.join(',')}, function (ret) {
                    if (ret.code === 1) {
                        Toastr.success(ret.msg);
                    } else {
                        Toastr.error(ret.msg);
                    }
                    parent.location.reload();
                });
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
