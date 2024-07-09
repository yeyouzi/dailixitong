define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user.user/index',
                    add_url: 'user.user/add',
                    edit_url: 'user.user/edit',
                    recharge_url: 'user.user/recharge',
                    multi_url: 'user.user/multi',
                    clear_all_url: 'user.user/clear_all',
                    clear_one_url: 'user.user/clear_one',
                    table: 'user',
                }
            });

            var table = $("#table");

            var selectPlatformId = Config.defaultPlatformId;

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                search: false,
                searchFormVisible: true,
                columns: [
                    [
                        {checkbox: true},
                        {
                            field: 'platform_id',
                            title: __('Platform_id'),
                            visible: false,
                            searchList: function (column) {
                                return Template('platformListTpl', {});
                            }
                        },
                        {
                            field: 'level_id',
                            title: __('Level_id'),
                            visible: false,
                            searchList: function (column) {
                                return Template('levelListTpl', {});
                            }
                        },
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'userAttribute.app_id', title: __('App_id'),operate: "="},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'mobile', title: __('Mobile'), operate: 'LIKE'},
                        {
                            field: 'gender',
                            title: __('Gender'),
                            operate: false,
                            formatter: Controller.api.formatter.gender
                        },
                        {
                            field: 'create_time',
                            title: __('Createtime'),
                            formatter: Table.api.formatter.datetime,
                            operate: false,
                            addclass: 'datetimerange',
                            sortable: true
                        },

                        {
                            field: 'userAttribute',
                            width: "120px",
                            title: __('Level'),
                            operate: false,
                            formatter: Controller.api.formatter.level
                        },
                        {field: 'userAttribute.money', title: __('Money'), operate: false},
                        {field: 'userAttribute.total_money', title: __('Total_money'), operate: false},
                        {
                            field: 'userAttribute',
                            title: __('Personal balance'),
                            operate: false,
                            formatter: Controller.api.formatter.balance
                        },
                        {
                            field: 'team_balance',
                            title: __('Team balance'),
                            operate: false,
                            formatter: Controller.api.formatter.team_balance
                        },

                        {
                            field: 'referee_id',
                            title: __('Referee_id'),
                            operate: false,
                            formatter: Controller.api.formatter.referee_id
                        },
                        {
                            field: 'referee_second_id',
                            title: __('Referee_second_id'),
                            operate: false,
                            formatter: Controller.api.formatter.referee_second_id
                        },

                        {field: 'first_num', title: __('Child_1'), operate: false},
                        {field: 'second_num', title: __('Child_2'), operate: false},
                        {
                            field: 'operate_list',
                            width: "220px",
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'edit',
                                    text: __('Edit'),
                                    title: __('Edit'),
                                    classname: 'btn btn-xs btn-success btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.edit_url + `/platformId/{$rows.platform_id}`,
                                },
                                {
                                    name: 'recharge',
                                    text: __('Recharge'),
                                    title: __('Recharge'),
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    url: $.fn.bootstrapTable.defaults.extend.recharge_url + `/platformId/{$rows.platform_id}`,
                                },
                                {
                                    name: 'clear_one',
                                    text: __('Clear one data'),
                                    title: __('Clear one data'),
                                    classname: 'btn btn-xs btn-danger btn-ajax',
                                    url: $.fn.bootstrapTable.defaults.extend.clear_one_url + `/platformId/{$rows.platform_id}`,
                                    confirm: __('Clear one data tips'),
                                    success: function (data, ret) {
                                        $('.btn-refresh').trigger('click');
                                    },
                                },
                            ],
                            formatter: Table.api.formatter.buttons
                        }
                    ]
                ]
            });

            //清除所有用户的佣金和业绩
            $('#clear-all-data').click(function () {
                layer.confirm(__('Clear All data tips'), {
                    btn: [__('OK'), __('Cancel')] //按钮
                }, function (index) {
                    var indexLoad = layer.load(1 , {shade: [0.5, '#000']});
                    Fast.api.ajax({
                        url: $.fn.bootstrapTable.defaults.extend.clear_all_url,
                        data: {platformId: Config.defaultPlatformId},
                        type: "POST",
                        dataType: "json",
                        loading: false,
                        success: function (ret) {
                            $('.btn-refresh').trigger('click');
                        },
                        error: function (ret) {
                        },
                        complete: function (ret){
                            layer.close(indexLoad)
                        }
                    });
                    layer.close(index);
                    return true;
                }, function () {
                    return true;
                });
            });


            //动态下拉的事件
            $("#c-level_id_select").data("params", function (obj) {
                return {custom: {platform_id: $("#c-platform_id_select").val()}};
            });
            $("#c-platform_id_select").change(function () {
                $('#c-level_id_select').selectPageClear();
            });


            //渲染完数据的回调
            table.on('load-success.bs.table', function (e, data) {
                Config.defaultPlatformId = data.platformId
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
        recharge: function () {
            $('#c-recharge_type').change(function () {
                let val = $(this).val();
                let currentValueObj = $('#c-current_value');
                if (val == 0) {
                    currentValueObj.val(currentValueObj.data('balance'))
                } else if (val == 1) {
                    currentValueObj.val(currentValueObj.data('money'))
                }
            })
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                gender: function (val) {
                    return __(`Gender${val}`)
                },
                referee_id: function (val, row, index) {
                    if (row.firstUser == null) {
                        return '-';
                    } else {
                        return `${row.firstUser.nickname}（ID:${row.firstUser.id}）`;
                    }
                },
                referee_second_id: function (val, row, index) {
                    if (row.secondUser == null) {
                        return '-';
                    } else {
                        return `${row.secondUser.nickname}（ID:${row.secondUser.id}）`;
                    }
                },
                level: function (val, row) {
                    if (val !== null && val.hasOwnProperty('level') && val.level !== null) {
                        return `${val.level.name}（级别数值：${val.level.value}）`;
                    }
                    return '-'
                },
                balance: function (val, row) {
                    if (val !== null) {
                        return val.balance;
                    }
                    return '0.00'
                },
                team_balance: function (val, row) {
                    return val
                },
            }
        }
    };
    return Controller;
});