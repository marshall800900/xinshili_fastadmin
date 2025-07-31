define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/systemtask/index' + location.search,
                    add_url: 'general/systemtask/add',
                    edit_url: 'general/systemtask/edit',
                    del_url: 'general/systemtask/del',
                    multi_url: 'general/systemtask/multi',
                    import_url: 'general/systemtask/import',
                    table: 'system_task',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'task_name', title: __('Task_name'), operate: false},
                        {field: 'task_value', title: __('Task_value'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'time_interval', title: __('Time_interval'), operate: false},
                        {field: 'last_task_time', title: __('Last_task_time'), operate:false, sortable:true, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'task_time', title: __('Task_time'), operate:false, sortable:true, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'is_open', title: __('Is_open'), searchList: {"0":__('Is_open 0'),"1":__('Is_open 1')}, formatter: Table.api.formatter.toggle},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                showToggle: false,
                showColumns: true,
                showExport: false,
                search: false,
                commonSearch: true,
                searchFormVisible: true,
                pageList: [10, 20, 50],
                pageSize: 50,
                stickyHeader:true,
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
