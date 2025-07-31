define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/mashangproduct/index' + location.search + '&admin_id=' + Config.admin_id,
                    add_url: 'mashangchannel/mashangproduct/add?admin_id=' + Config.admin_id,
                    edit_url: 'mashangchannel/mashangproduct/edit',
                    // del_url: 'mashangchannel/mashangproduct/del',
                    multi_url: 'mashangchannel/mashangproduct/multi',
                    import_url: 'mashangchannel/mashangproduct/import',
                    table: 'mashang_product',
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
                        {field: 'receiving_account_code', title: __('Receiving_account_code'), operate: 'LIKE'},
                        {field: 'admin_id', title: __('Admin_id'), operate:false},
                        {field: 'rate', title: __('Rate'), operate:false},
                        {field: 'width', title: __('Width'), operate:false},
                        {field: 'is_open', title: __('Is_open'), searchList: {"0":__('Is_open 0'),"1":__('Is_open 1')}, formatter: Table.api.formatter.toggle},
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
                stickyHeader: true,
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
