define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'refuse/index' + location.search,
                    // add_url: 'refuse/add',
                    // edit_url: 'refuse/edit',
                    // del_url: 'refuse/del',
                    // multi_url: 'refuse/multi',
                    // import_url: 'refuse/import',
                    table: 'refuse',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                searchFormVisible:true,
                showToggle:false,
                showColumns:false,
                showExport:false,
                search:false,
                showSearch: false,
                exportOptions: {
                    fileName: $.fn.bootstrapTable.defaults.extend.table + Math.round(Math.random()*100) + '_' + Moment().format("YYYY-MM-DD"),
                    ignoreColumn: [0, 'operate','detail'] //默认不导出第一列(checkbox)与操作(operate)列
                },

                columns: [
                    [
                        // {checkbox: true},
                        {field: 'detail', title: __('详情'), table: table,
                            buttons: [
                                {
                                    name: 'base',
                                    text:__('员工信息'),
                                    title: __('员工信息'),
                                    //图标
                                    icon: 'fa fa-user-circle',
                                    //btn-dialog表示为弹窗
                                    classname: 'btn btn-xs btn-default',
                                    //弹窗位置，//自带参数ids
                                    url: 'employee/detail',
                                    //弹窗大小
                                    extend: 'data-area=\'["100%","100%"]\', target="_blank"',

                                }
                            ], operate:false, formatter: Table.api.formatter.buttons
                        },
                        {field: 'emp_id', title: __('Emp_id'), operate: 'LIKE'},
                        {field: 'emp_id_2', title: __('Emp_id_2'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'),searchList: Config.status_list, formatter: Table.api.formatter.status},
                        {field: 'emp_name', title: __('Emp_name'), operate: 'LIKE'},
                        {field: 'sex', title: __('Sex'), searchList: {"男":__('男'),"女":__('女')}, formatter: Table.api.formatter.normal},
                        {field: 'education', title: __('Education'), operate: false},
                        {field: 'id_card', title: __('Id_card')},
                        {field: 'emp_source', title: __('Emp_source'), searchList: Config.source_list, formatter: Table.api.formatter.normal},
                        {field: 'tel', title: __('Tel')},
                        {field: 'marry', title: __('Marry'), searchList: Config.marry_list, formatter: Table.api.formatter.normal},
                        {field: 'age', title: __('Age'), operate: false},
                        {field: 'come_date', title: __('Come_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'kq_date', title: __('Kq_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'exam_time', title: '体检时间', operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'address', title: '身份证地址',operate: false},
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
        // add: function () {
        //     Controller.api.bindevent();
        // },
        // edit: function () {
        //     Controller.api.bindevent();
        // },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
