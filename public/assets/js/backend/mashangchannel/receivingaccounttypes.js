define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'mashangchannel/receivingaccounttypes/index',
                    multi_url: '',
                }
            });

            var table = $("#table");

            Template.helper("Moment", Moment);

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                templateView: true,
                columns: [
                    [
                    ],
                ],
                showToggle: false,
                showColumns: false,
                showExport: false,
                search: false,
                commonSearch: false,
                searchFormVisible: false,
                pageList: [10, 20, 50],
                pageSize: 50,
                stickyHeader:false,
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            //指定搜索条件
            $(document).on("click", ".btn-toggle-view", function () {
                var options = table.bootstrapTable('getOptions');
                table.bootstrapTable('refreshOptions', {templateView: !options.templateView});
            });

            //点击详情
            $(document).on("click", ".btn-detail[data-id]", function () {
                Backend.api.open('example/bootstraptable/detail/ids/' + $(this).data('id'), __('Detail'));
            });

            //获取选中项
            $(document).on("click", ".btn-selected", function () {
                //在templateView的模式下不能调用table.bootstrapTable('getSelections')来获取选中的ID,只能通过下面的Table.api.selectedids来获取
                Layer.alert(JSON.stringify(Table.api.selectedids(table)));
            });
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
            formatter: {
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                ip: function (value, row, index) {
                    return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
                },
                browser: function (value, row, index) {
                    //这里我们直接使用row的数据
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                }
            },
            events: {
                ip: {
                    'click .btn-ip': function (e, value, row, index) {
                        var options = $("#table").bootstrapTable('getOptions');
                        //这里我们手动将数据填充到表单然后提交
                        $("#commonSearchContent_" + options.idTable + " form [name='ip']").val(value);
                        $("#commonSearchContent_" + options.idTable + " form").trigger('submit');
                        Toastr.info("执行了自定义搜索操作");
                    }
                },
                browser: {
                    'click .btn-browser': function (e, value, row, index) {
                        Layer.alert("该行数据为: <code>" + JSON.stringify(row) + "</code>");
                    }
                }
            }
        }
    };
    return Controller;
});