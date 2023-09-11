{% if pagingInfo['row_count'] > 0 %}
    <div class="float-right">
        <ul class="pagination">
            <li class="page-item previous"><a class="page-link" href="{{ pagingInfo['current_link'] }}&p=1"><span>First</span></a></li>
            <li class="page-item"><a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['page']-1<=1?1:pagingInfo['page']-1 }}"><span>«</span></a></li>
            {% for index,item in pagingInfo['range_page'] %}
                <li class="page-item page-{{ index+1 }} {{ item==pagingInfo['page']?"active":"" }}"><a class="page-link {{ item==pagingInfo['page']?"active":"" }}" href="{{ pagingInfo['current_link'] }}&p={{ item }}">{{ item }}</a></li>
            {% endfor %}
            <li class="page-item"><a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['page']+1>=pagingInfo['total_page']?pagingInfo['total_page']:pagingInfo['page']+1 }}"><span>»</span></a></li>
            <li class="page-item next"><a class="page-link" href="{{ pagingInfo['current_link'] }}&p={{ pagingInfo['total_page'] }}"><span>Last</span></a></li>
        </ul>
    </div>
{% else %}
    <div class="text-center">
        <p class="m-t-10">No Record Available</p>
    </div>
{% endif %}
