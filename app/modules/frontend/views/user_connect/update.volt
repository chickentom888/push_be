<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User Connect</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Update User Connect</h5>

                    <a href="/user_connect" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input class="form-control" type="text" value="{{ object['address'] }}" id="address" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="code">Code</label>
                                        <input class="form-control" type="text" value="{{ object['code'] }}" id="code" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="branch">Branch</label>
                                        <input class="form-control" type="text" value="{{ object['branch']|length ? object['branch']|upper : '' }}" id="branch" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="branch_for_child">Child branch</label>
                                        <input class="form-control" type="text" value="{{ object['branch_for_child']|length ? object['branch_for_child']|upper : '' }}" id="branch_for_child" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="inviter_address">Inviter address</label>
                                        <input class="form-control" type="text" value="{{ inviter['address'] }}" id="inviter_address" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="inviter_code">Inviter code</label>
                                        <input class="form-control" type="text" value="{{ inviter['code'] }}" id="inviter_code" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="parent_address">Parent address</label>
                                        <input class="form-control" type="text" value="{{ parent['address'] }}" id="parent_address" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="parent_code">Parent code</label>
                                        <input class="form-control" type="text" value="{{ parent['code'] }}" id="parent_code" disabled>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="created_at">Created at</label>
                                        <input class="form-control" type="text" value="{{ date('d/m/Y H:i:s', object['created_at']) }}" id="created_at" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="diagram_date">Diagram date</label>
                                        <input class="form-control" type="text" value="{{ date('d/m/Y H:i:s', object['diagram_date']) }}" id="diagram_date" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="coin_balance">Coin balance</label>
                                        <input class="form-control" type="text" value="{{ helper.numberFormat(object['coin_balance'], 4) }}" id="coin_balance" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="interest_balance">Interest balance</label>
                                        <input class="form-control" type="text" value="{{ helper.numberFormat(object['interest_balance'], 4) }}" id="interest_balance" disabled>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="lock_withdraw">Lock withdraw</label>
                                        <select name="lock_withdraw" class="form-control" id="lock_withdraw">
                                            <option value="0" {{ object['lock_withdraw']|length AND object['lock_withdraw'] == 0 ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ object['lock_withdraw']|length AND object['lock_withdraw'] == 1 ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>