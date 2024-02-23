define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'employee/index' + location.search,
                    add_url: 'employee/add',
                    // edit_url: 'employee/edit',
                    del_url: 'employee/del',
                    multi_url: 'employee/multi',
                    import_url: 'employ/import',
                    table: 'employee',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'status',
                sortOrder: 'asc',
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
                        // {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'come_date', title: __('Come_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'kq_date', title: __('Kq_date'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'exam_time', title: '体检时间', operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,visible:false},
                        {field: 'address', title: '身份证地址'},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'roles',
                                    text:__('权限'),
                                    title: __('权限'),
                                    //图标
                                    icon: 'fa fa-user-plus',
                                    //btn-dialog表示为弹窗
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    //弹窗位置，//自带参数ids
                                    url: 'employee/roles?emp2={row.emp_id_2}',
                                    //弹窗大小
                                    extend: 'data-area=\'["100%","100%"]\'',
                                    visible: function (row) {
                                        if (row.status > 0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                                {
                                    name: 'roles',
                                    text:__('打印'),
                                    title: __('打印'),
                                    //图标
                                    icon: 'fa fa-file',
                                    //btn-dialog表示为弹窗
                                    classname: 'btn btn-xs btn-out btn-dialog',
                                    //弹窗位置，//自带参数ids
                                    url: 'pdf/make?emp2={row.emp_id_2}',
                                    //弹窗大小
                                    extend: 'data-area=\'["100%","100%"]\'',
                                    visible: function (row) {
                                        if (row.status > 0){
                                            return true;
                                        }
                                        return false;
                                    }
                                },
                            ],
                            formatter: function(value, row, index){
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                if (row.status == 3 || row.status == 4) {
                                    $(table).data("operate-del", null);
                                }
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            },
                        },
                    ]
                ]
                ,onLoadSuccess: function(){
                    $('.search , .columns-right').hide();
                    $('.btn_export').unbind('click').click(function(){
                        $('.dropdown-menu li[data-type="excel"]').trigger('click');
                    });
                    $('#ctime').attr('autocomplete','off');
                    //报道
                    $('.btn_report').unbind('click').click(function(){
                        //sessionStorage.removeItem('device');
                        var device = sessionStorage.getItem('device');
                        if (device !== null) {
                            $.get(base_file+"/employee/readCard?device="+device, function (ret) {
                                if (ret.code === 1) {
                                    Fast.api.open(
                                        'employee/report?id_card='+ret.data.id,
                                        '报到详情',
                                        {area:["100%", "100%"]
                                        });
                                } else {
                                    Toastr.error(ret.msg);
                                }
                            });
                        } else {
                            //没有绑定设备弹窗
                            Toastr.error(__('设备暂未绑定，请先绑定后操作~'));
                            Fast.api.open("employee/device", '设备绑定', {area: ["70%", "70%"]});
                        }
                    });
                    //报名
                    $('.btn_sign').click(function(){
                        var device = sessionStorage.getItem('device');
                        if (device !== null) {
                            $.get(base_file+"/employee/readCard?flag=sign&device="+device, function (ret) {
                                if (ret.code === 1) {
                                    if (ret.data.is == 0) {
                                        //新增
                                        Fast.api.open(
                                            'employee/add',
                                            '添加',
                                            {area:["100%", "100%"]
                                            });
                                    } else {
                                        Fast.api.open(
                                            'employee/sign?id_card='+ret.data.id,
                                            '报名确认',
                                            {area:["100%", "100%"]
                                            });
                                    }
                                } else {
                                    Toastr.error(ret.msg);
                                }
                            });
                        } else {
                            //没有绑定设备弹窗
                            Toastr.error(__('设备暂未绑定，请先绑定后操作~'));
                            Fast.api.open("employee/device", '设备绑定', {area: ["70%", "70%"]});
                        }
                    });
                    //导出
                    $('#btn-export-table').unbind('click').click(function(){
                        var source = $("select[name='emp_source']").val();
                        if (source.length == 0) {
                            Toastr.error('请选择员工类型');
                            return false;
                        }

                        var jsonData = JSON.stringify($('form').serializeArray());
                        // console.log();return;

                        var url = base_file+"/employ/export?filter="+btoa(encodeURI(jsonData));
                        window.open(url, '_blank');
                    });
                }
            });
            //体检导入
            require(['upload'], function(Upload){
                Upload.api.plupload('#plupload-files', function (data, ret) {
                    if (ret.code == 1) {
                        $.get("employ/import",{"file":ret.data.url}, function (rs) {
                            $(".btn-refresh").trigger("click"); // 触发窗体页面刷新
                            Toastr.success(rs.msg);
                            setTimeout(function(){
                                if (rs.data.err_num > 0) {
                                    //失败弹窗
                                    Fast.api.open("exam_log/lists?time="+rs.data.time, '失败记录', {area:["100%", "100%"]});
                                }
                            }, 2000);
                        });
                    }
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
        roles: function () {
            Controller.api.bindevent();
        },
        exam: function () {
            Controller.api.bindevent();
        },
        report: function () {
            Controller.api.bindevent();
        },
        sign: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        },

    };
    return Controller;
});
