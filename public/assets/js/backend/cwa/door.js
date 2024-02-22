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
                // pk: '日期',
                // sortName: '日期',
                // sortOrder: 'desc',
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
                                    // {
                                    //     name: 'read',
                                    //     text:__('查看'),
                                    //     title: __('查看'),
                                    //     //图标
                                    //     icon: 'fa fa-external-link',
                                    //     //btn-dialog表示为弹窗
                                    //     classname: 'btn btn-xs btn-info btn-dialog',
                                    //     //弹窗大小
                                    //     extend: 'data-area=\'["100%","100%"]\'',
                                    //     url: 'cwa/cater_b/detail?date={row.日期}&area={row.食堂}',
                                    // },
                                ], operate:false, formatter: Table.api.formatter.buttons
                        },
                        {field: '工号', title: __('工号'), operate: 'like'},
                        {field: '姓名', title: __('姓名'), operate: 'like'},
                        {field: '打卡日期', title: __('打卡日期'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: '打卡时间', title: __('打卡时间'), operate: false},
                        {field: '安装地址', title: __('安装地址'), operate: false},
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
