<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Block Task</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Block Task</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/block/createBlockTask" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="status" id="id_status" class="form-control">
                                    <option value="">Status</option>
                                    <option value="0" {{ dataGet['status']|length and dataGet['status'] == '0' ? 'selected' : '' }}>Not Scan</option>
                                    <option value="1" {{ dataGet['status']|length and dataGet['status'] == '1' ? 'selected' : '' }}>Scanning</option>
                                    <option value="2" {{ dataGet['status']|length and dataGet['status'] == '2' ? 'selected' : '' }}>Scanned</option>
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <input placeholder="Block" id="block" type="text" class="form-control" name="block" value="{{ dataGet['block'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-2">
                        Total: {{ helper.numberFormat(count) }}
                    </div>

                    <form action="/block/deleteBlockTask" method="post">
                        <div class="mt-2 mb-2">
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="checkedAll">
                                    </th>
                                    <th class="border-top-0">Platform</th>
                                    <th class="border-top-0">Block</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0">Date</th>
                                    <th class="border-top-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                {% for item in listData %}
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkSingle" name="id[]" value="{{ item['_id'] }}">
                                        </td>
                                        <td>
                                            <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                            <div>Network: <b>{{ item['network']|upper }}</b></div>
                                        </td>
                                        <td>
                                            <b>{{ item['block'] }}</b>
                                        </td>
                                        <td>
                                            <b>{{ helper.getBlockTaskStatusText(item['status']) }}</b>
                                        </td>
                                        <td>
                                            <div>Created: <b>{{ date("d-m-Y H:i:s", item['created_at']) }}</b></div>
                                            <div>Processed: <b>{{ date("d-m-Y H:i:s", item['processed_at']) }}</b></div>
                                        </td>
                                        <td>
                                            <a href="/block/deleteBlockTask/{{ item['_id'] }}" class="btn btn-sm btn-danger">Delete</a>

                                            {% if item['status'] == 1 %}
                                                <a href="/block/changeStatusBlockTask/{{ item['_id'] }}/0" class="btn btn-sm btn-info">Process</a>
                                            {% endif %}
                                        </td>
                                    </tr>
                                {% endfor %}

                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#checkedAll").change(function () {
            if (this.checked) {
                $(".checkSingle").each(function () {
                    this.checked = true;
                });
            } else {
                $(".checkSingle").each(function () {
                    this.checked = false;
                });
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")) {
                let isAllChecked = 0;

                $(".checkSingle").each(function () {
                    if (!this.checked)
                        isAllChecked = 1;
                });

                if (isAllChecked == 0) {
                    $("#checkedAll").prop("checked", true);
                }
            } else {
                $("#checkedAll").prop("checked", false);
            }
        });
    })
</script>