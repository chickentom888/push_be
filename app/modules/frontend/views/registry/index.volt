{{ flash.output() }}
{% for key,item in data %}
    <h3>List {{ key|upper }} Block Need To Scan</h3>
    <div><a href="/registry/add_block_scan/{{ key }}">Add block</a></div>
    <div class="form-group">&nbsp;
        {% for val in item %}
            {{ val }},
        {% endfor %}
    </div>
{% endfor %}

