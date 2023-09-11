<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Presale User Log</h4>
            </div>
        </div>
    </div>
</div>
{% if pool %}
    {% include 'layouts/pool/pool_info.volt' %}
    {% include 'layouts/pool/pool_user_log.volt' %}
{% else %}
    <div class="text-center">
        <p class="m-t-10">No Record Available</p>
    </div>
{% endif %}