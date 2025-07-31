define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payconfig/receivingaccounttypes/index' + location.search,
                    add_url: 'payconfig/receivingaccounttypes/add',
                    edit_url: 'payconfig/receivingaccounttypes/edit',
                    del_url: 'payconfig/receivingaccounttypes/del',
                    multi_url: 'payconfig/receivingaccounttypes/multi',
                    import_url: 'payconfig/receivingaccounttypes/import',
                    table: 'receiving_account_types',
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
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'code', title: __('Code'), operate: '='},
                        {field: 'name', title: __('Name'), operate:false},
                        {field: 'day_receiving_number', title: __('Day_receiving_number'), operate:false},
                        {field: 'day_receiving_amount', title: __('Day_receiving_amount'), operate:false},
                        {field: 'fail_pay_number', title: __('Fail_pay_number'), operate:false},
                        {field: 'create_time', title: __('Create_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'update_time', title: __('Update_time'), operate:false, addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
