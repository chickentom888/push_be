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
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Slide management </h5>

                    <a href="/slide" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input class="form-control" type="text" name="title"
                                               value="{{ object['title'] }}" id="title" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="language">Language</label>
                                        <select name="language" class="form-control" id="language">
                                            {% for key, item in listLanguage %}
                                                <option value="{{ key }}" {{ object['language'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="group">Group</label>
                                        <input class="form-control" type="text" name="group"
                                               value="{{ object['group'] }}" id="group" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="link">Link</label>
                                        <input class="form-control" type="url" name="link" value="{{ object['link'] }}"
                                               id="link">
                                    </div>
                                    <div class="form-group">
                                        <label for="url_img">Image url</label>
                                        <input class="form-control" type="url" name="url_img" value="{{ object['url_img'] }}"
                                               id="url_img">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>