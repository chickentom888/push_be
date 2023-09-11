{% if pagingInfo['row_count'] > 0 %}
    <div class="text-center">

        <p class="text-left mt-2">Total: {{ pagingInfo['row_count'] }} &nbsp;</p>
        <ul class="pagination mb-0">
            <li class="page-item">
                <a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['page'] -1 <= 1 ? 1 : pagingInfo['page'] - 1 }}">&leftarrow;</a>
            </li>

            {% set rangeLength = pagingInfo['range_page']|length %}
            {% set firstItem = pagingInfo['range_page'][0] %}
            {% set lastItem = pagingInfo['range_page'][rangeLength-1] %}

            {% if lastItem - rangeLength > 1 %}
                <li class="page-item">
                    <a class="page-link" href="{{ pagingInfo['current_link'] }}&p=1"><span>1</span></a>
                </li>
            {% endif %}

            {% if firstItem > 2 %}
                <li class="page-item">
                    <a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ firstItem - 1 }}"><span>...</span></a>
                </li>
            {% endif %}

            {% for index,item in pagingInfo['range_page'] %}
                <li class="page-item {{ item == pagingInfo['page'] ? "active" : "" }}">
                    <a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ item }}">{{ item }}</a>
                </li>
            {% endfor %}

            {% if pagingInfo['total_page'] - 1 > lastItem %}
                <li class="page-item"><a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ lastItem + 1 }}">...</a></li>
            {% endif %}

            {% if lastItem < pagingInfo['total_page'] %}
                <li class="page-item">
                    <a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['total_page'] }}"><span>{{ pagingInfo['total_page'] }}</span></a>
                </li>
            {% endif %}

            <li class="page-item">
                <a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['page'] + 1 >= pagingInfo['total_page'] ? pagingInfo['total_page'] : pagingInfo['page'] + 1 }}">
                    &rightarrow;
                </a>
            </li>
        </ul>
    </div>
{% else %}
    <div class="text-center"><p class="text-success m-t-10">No record</p></div>
{% endif %}
