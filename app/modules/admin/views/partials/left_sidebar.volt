<div class="vertical-menu">
    <div data-simplebar="" class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                {% if sidebars %}
                    {% for sidebar in sidebars %}
                        {% if in_array(userInfo.role,sidebar['role']) %}
                            <li class="{% if sidebar['active_menu']==active_menu %} mm-active {% endif %}">
                                <a href="{{ sidebar['link'] }}" class="{% if sidebar['active_menu']==active_menu %} mm-active {% endif %} {% if sidebar['child'] %}has-arrow{% endif %} waves-effect">
                                    <i class="{{ sidebar['icon'] }}"></i>
                                    <span>{{ sidebar['name'] }}</span>
                                </a>
                                {% if sidebar['child'] %}
                                    <ul class="sub-menu" aria-expanded="false">
                                        {% for child in sidebar['child'] %}
                                            {% if in_array(userInfo.role,child['role']) %}
                                                <li class="{{ child['active_menu']==active_sub_menu ? 'active mm-active':'' }}">
                                                    <a href="{{ child['link'] }}" class="{{ child['active_menu']==active_sub_menu ? 'active':'' }}"> {{ child['name'] }}</a>
                                                </li>
                                            {% endif %}
                                        {% endfor %}
                                    </ul>
                                {% endif %}
                            </li>
                        {% endif %}
                    {% endfor %}
                {% endif %}
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
