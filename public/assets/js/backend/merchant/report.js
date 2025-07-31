define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: async function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'merchant/report/index' + location.search,
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'date_key',
                sortName: 'date_key',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {field: 'date_key', title: __('Date_key'), operate:'RANGE', addclass:'datetimerange', autocomplete:false,sortable: true},
                        {field: 'product_code', title: __('Product_code'),
                            searchList: await $.getJSON('getselectpage/getPayProduct?is_json=2'),
                            formatter: Table.api.formatter.status
                        },
                        // {field: 'merchant_id', title: __('Merchant_id')},
                        {
                            field: 'create_order_number',
                            title: __('Create_order_number') + '/' + __('Create_order_amount'),
                            operate: false,
                            sortable: true,
                            formatter: function (value, row, index) {
                                return row.create_order_number + '&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;' + row.create_order_amount;
                            }
                        },
                        {
                            field: 'success_order_number',
                            title: __('Success_order_number') + '/' + __('Success_order_amount'),
                            operate: false,
                            sortable: true,
                            formatter: function (value, row, index) {
                                return row.success_order_number + '&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;' + row.success_order_amount;
                            }
                        },
                        {field: 'merchant_rate_amount', title: __('Merchant_rate_amount'), operate: false},
                        {field: 'real_back_amount', title: __('Real_back_amount'), operate: false}
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
