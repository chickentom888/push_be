<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Presale User Log</h4>
            </div>
        </div>
    </div>
</div>

{% if presale %}
    {% include 'layouts/presale_info.volt' %}
    {% include 'layouts/presale_user_log.volt' %}
{% else %}
    <div class="text-center">
        <p class="m-t-10">No Record Available</p>
    </div>
{% endif %}