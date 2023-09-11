{{ flash.output() }}
<h2>Sync Status</h2>
<div class="card bg-white m-b-30">
    <div class="card-body">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Token</th>
                <th>Platform</th>
                <th>Last block</th>
                <th>Last updated time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            {% for item in cronInfo %}
                <tr>
                    <td>{{ item.token_key }}</td>
                    <td>{{ item.platform }}</td>
                    <td>{{ item.last_block }}</td>
                    <td>{{ date("d-m-Y H:i:s",item.updated_at) }}</td>
                    <td>{{ redis.get(item.platform~"_main_syncing") }}</td>
                    <td>
                        <a href="/index/reset_sync/{{ item.token_key }}">Reset</a> |
                        <a href="/index/reset_sync/{{ item.token_key }}/1">Set to syncing</a>
                    </td>
                </tr>
            {% endfor %}
            </tbody>
        </table>
    </div>
</div>
