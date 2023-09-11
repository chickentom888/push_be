<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0 font-size-18">{{ header['title'] }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    {% if sidebars %}
                        {% for sidebar in sidebars %}
                            {% if sidebar['active_menu']==active_menu %}
                                <li class="breadcrumb-item"><a href="javascript:void(0);">{{ sidebar['name'] }}</a></li>
                                {% if sidebar['child'] %}
                                    {% for child in sidebar['child'] %}
                                        {% if child['active_menu']==active_sub_menu %}
                                            <li class="breadcrumb-item active" aria-current="page"><span>{{ child['name'] }}</span></li>
                                        {% endif %}
                                    {% endfor %}
                                {% endif %}
                            {% endif %}
                        {% endfor %}
                    {% endif %}
                </ol>
            </div>

        </div>
    </div>
</div>
