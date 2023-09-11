<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Slide management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Slide management</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/slide/form" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2">
                                <select name="language" id="language" class="form-control">
                                    <option value="">Language</option>
                                    {% for key,item in listLanguage %}
                                        <option value="{{ key }}" {{ dataGet['language'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <input placeholder="slide title" id="search" type="text" class="form-control"
                                       name="title"
                                       value="{{ dataGet['title'] }}">
                            </div>
                            <div class="col-sm-2">
                                <input placeholder="group" id="group" type="text" class="form-control"
                                       name="group"
                                       value="{{ dataGet['group'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Title</th>
                                <th class="border-top-0">Language</th>
                                <th class="border-top-0">Group</th>
                                <th class="border-top-0">Link</th>
                                <th class="border-top-0">Image</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>{{ item['title'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['language']|upper }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['group'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['link'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['url_img'] }}</div>
                                    </td>
                                    <td>
                                        <a href="/slide/form/{{ item['_id'] }}" class="btn btn-info btn-sm">Edit</a>
                                        <a href="/slide/delete/{{ item['_id'] }}"
                                           class="btn btn-danger btn-sm need-confirm">Delete</a>
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
