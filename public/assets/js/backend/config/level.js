define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'config.level/index' + location.search,
                    add_url: 'config.level/add',
                    edit_url: 'config.level/edit',
                    del_url: 'config.level/del',
                    multi_url: 'config.level/multi',
                    platform_list_url: 'config.platform/getList',
                    table: 'level',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'value',
                sortOrder: 'asc',
                searchFormVisible: true,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false , visible: false},
                        {
                            field: 'platform_id',
                            title: __('Platform.name'),
                            visible: false,
                            searchList: function (column) {
                                return Template('platformListTpl', {});
                            }
                        },
                        {field: 'value', title: __('Value'),operate: '='},
                        {field: 'name', title: __('Name'),operate: 'LIKE %...%'},
                        {field: 'percent', title: __('Percent'),operate: false},
                        {field: 'second_percent', title: __('Second_percent'),operate: false},
                        {
                            field: 'platform.name',
                            title: __('Platform.name'),
                            operate: false,
                            formatter: Table.api.formatter.label
                        },
                        {
                            field: 'update_time',
                            title: __('update_time'),
                            operate: false,
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
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
            },
            formatter:{
                platformList : function (val , rows , index){
                    console.log(val)
                }
            }
        }
    };
    return Controller;
});