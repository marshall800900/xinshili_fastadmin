define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/mashangreport/index' + location.search,
                    add_url: 'mashangchannel/mashangreport/add',
                    edit_url: 'mashangchannel/mashangreport/edit',
                    del_url: 'mashangchannel/mashangreport/del',
                    multi_url: 'mashangchannel/mashangreport/multi',
                    import_url: 'mashangchannel/mashangreport/import',
                    table: 'mashang_report',
                }
            });

            var table = $("#table");
            table.on('load-success.bs.table', function (e, data) {
                let html = '';
                html += '<span class="money_sum_lable" title="">'+__('Create_order_number') +'</span><span  class="money_sum_green" >'+data.counts.create_order_number+'</span><span class="money_sum_lable" title="">笔 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Create_order_amount') +'</span><span  class="money_sum_green" >'+data.counts.create_order_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Success_order_number') +'</span><span  class="money_sum_green" >'+data.counts.success_order_number+'</span><span class="money_sum_lable" title="">笔 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Success_order_amount') +'</span><span  class="money_sum_green" >'+data.counts.success_order_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('From_success_order_number') +'</span><span  class="money_sum_green" >'+data.counts.from_success_order_number+'</span><span class="money_sum_lable" title="">笔 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('From_success_order_amount') +'</span><span  class="money_sum_green" >'+data.counts.from_success_order_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Team_success_order_number') +'</span><span  class="money_sum_green" >'+data.counts.team_success_order_number+'</span><span class="money_sum_lable" title="">笔 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Team_success_order_amount') +'</span><span  class="money_sum_green" >'+data.counts.team_success_order_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Rate_amount') +'</span><span  class="money_sum_green" >'+data.counts.rate_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('from_rate_amount') +'</span><span  class="money_sum_green" >'+data.counts.from_rate_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                html += '<span class="money_sum_lable" title="">'+__('Team_rate_amount') +'</span><span  class="money_sum_green" >'+data.counts.team_rate_amount+'</span><span class="money_sum_lable" title="">元 &nbsp;&nbsp;&nbsp;&nbsp;</span>';
                $('.counts').html(html);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'date_key',
                sortName: 'date_key',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {field: 'date_key', title: __('Date_key'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'pid', title: __('Pid')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {
                            field: 'create_order_number',
                            title: __('Create_order_number') + '/' + __('Create_order_amount'),
                            operate: false,
                            sortable: true,
                            align: 'left',
                            formatter: function (value, row, index) {
                                return row.create_order_number + '&nbsp;/&nbsp;' + row.create_order_amount;
                            }
                        },
                        {
                            field: 'success_order_number',
                            title: __('Success_order_number') + '/' + __('Success_order_amount'),
                            operate: false,
                            sortable: true,
                            align: 'left',
                            formatter: function (value, row, index) {
                                return row.success_order_number + '&nbsp;/&nbsp;' + row.success_order_amount;
                            }
                        },
                        {
                            field: 'from_success_order_number',
                            title: __('From_success_order_number') + '/' + __('From_success_order_amount'),
                            operate: false,
                            sortable: true,
                            align: 'left',
                            formatter: function (value, row, index) {
                                return row.from_success_order_number + '&nbsp;/&nbsp;' + row.from_success_order_amount;
                            }
                        },
                        {
                            field: 'team_success_order_number',
                            title: __('Team_success_order_number') + '/' + __('Team_success_order_amount'),
                            operate: false,
                            sortable: true,
                            align: 'left',
                            formatter: function (value, row, index) {
                                return row.team_success_order_number + '&nbsp;/&nbsp;' + row.team_success_order_amount;
                            }
                        },
                        {field: 'rate_amount', title: __('Rate_amount'), operate: false},
                        {field: 'from_rate_amount', title: __('From_rate_amount'), operate: false},
                        {field: 'team_rate_amount', title: __('Team_rate_amount'), operate: false},
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
