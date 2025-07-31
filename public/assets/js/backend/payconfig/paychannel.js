define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: async function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'payconfig/paychannel/index' + location.search,
                    add_url: 'payconfig/paychannel/add',
                    edit_url: 'payconfig/paychannel/edit',
                    del_url: 'payconfig/paychannel/del',
                    multi_url: 'payconfig/paychannel/multi',
                    import_url: 'payconfig/paychannel/import',
                    table: 'pay_channel',
                }
            });

            var table = $("#table");

            table.on('post-body.bs.table', function () {
                $(".btn-editone").data("area", ["80%", "80%"]);
                $(".btn-addone").data("area", ["80%", "80%"]);
            })

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'pay_api_id', title: __('Pay_api_id'),operate: false},
                        {field: 'pay_channel_code', title: __('Pay_channel_code'), operate: false},
                        {field: 'receiving_account_code', title: __('Receiving_account_code'), operate: false},
                        {field: 'receiving_account_id', title: __('Receiving_account_id'), operate: false},
                        {
                            field: 'pay_type',
                            title: __('Pay_type'),
                            searchList: await $.getJSON('payconfig/paychannel/getSearchList?model_name=getPayTypeList'),
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'get_pay_url_type',
                            title: __('Get_pay_url_type'),
                            searchList: await $.getJSON('payconfig/paychannel/getSearchList?model_name=getGetPayUrlTypeList'),
                            operate: false,
                            formatter: Table.api.formatter.normal
                        },
                        {field: 'min_amount', title: __('Min_amount'), operate: false},
                        {field: 'max_amount', title: __('Max_amount'), operate: false},
                        {
                            field: 'amount_type',
                            title: __('Amount_type'),
                            searchList: await $.getJSON('payconfig/paychannel/getSearchList?model_name=getAmountTypeList'),
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'fix_amount',
                            title: __('Fix_amount'),
                            operate: false,
                            table: table,
                            class: 'autocontent',
                            formatter: Table.api.formatter.content
                        },
                        {field: 'cost_rate', title: __('Cost_rate'), operate: false},
                        {
                            field: 'is_open',
                            title: __('Is_open'),
                            searchList: await $.getJSON('payconfig/paychannel/getSearchList?model_name=getIsOpenList'),
                            formatter: Table.api.formatter.toggle,
                            operate: false
                        },
                        {
                            field: 'create_time',
                            title: __('Create_time'),
                            operate: false,
                            addclass: 'datetimerange',
                            autocomplete: false,
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'update_time',
                            title: __('Update_time'),
                            operate: false,
                            addclass: 'datetimerange',
                            autocomplete: false,
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

                toggleFixAmount();

                function toggleFixAmount(){
                    var amount_type = $("select[name='row[amount_type]").val();
                    if (amount_type == 2){
                        $('.fix_amount').show();
                    }else{
                        $('.fix_amount').hide();
                    }
                    return;
                }

                $(document).on("change", "select[name='row[amount_type]']", function () {
                    toggleFixAmount();
                });
            }
        }
    };
    return Controller;
});
