<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lottery Buy Log</h4>
            </div>
        </div>
    </div>
</div>

{% if lottery %}
    {% include 'layouts/lottery/lottery_info.volt' %}
    {% include 'layouts/lottery/lottery_buy_log.volt' %}
{% else %}
    <div class="text-center">
        <p class="m-t-10">No Record Available</p>
    </div>
{% endif %}