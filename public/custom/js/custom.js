$(document).ready(function ($) {
    $(document).on('click', '.need-confirm', function () {
        return confirm('Are you sure');
    });

    $('.sort-button').on('click', function () {
        let field_sort = $(this).attr('data-field-sort');
        let type_sort = $(this).attr('data-type-sort');
        let pathName = window.location.pathname;
        let query = window.location.search;
        let urlParsed = parseUrl(query);
        let url = "?";
        let fieldIgnore = ["field_sort", "type_sort"];
        for (let x in urlParsed) {
            if (fieldIgnore.indexOf(x) < 0) {
                url += x + "=" + urlParsed[x] + "&";
            }
        }

        url += "field_sort=" + field_sort + "&type_sort=" + type_sort;
        window.location.href = pathName + url;
    });

    $('.number').on('keyup', function () {
        let parts = this.value.split('.');
        parts[0] = parts[0].replace(/,/g, '').replace(/^0+/g, '');
        if (parts[0] === '') {
            parts[0] = '0';
        }
        let calculated = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
        if (parts.length >= 2) {
            calculated += '.' + parts[1].substring(0, 4);
        }
        this.value = calculated;
        if (this.value === 'NaN' || this.value === '') {
            this.value = 0;
        }
    });

    $('.input_copy_btn').click(function () {
        var input_group = $(this).closest('.input-group');
        var text_need_copy = input_group.find('.text_need_copy');
        text_need_copy.select();
        document.execCommand('copy');
        toastr.success("Copied!");
    });

});

function parseUrl(url = "") {
    let urlParams = {};
    let match,
        pl = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(s.replace(pl, " "));
        };
    let query = url.length ? url : window.location.search;
    query = query.substring(1);

    while (match = search.exec(query)) {
        if (decode(match[1]) in urlParams) {
            if (!Array.isArray(urlParams[decode(match[1])])) {
                urlParams[decode(match[1])] = [urlParams[decode(match[1])]];
            }
            urlParams[decode(match[1])].push(decode(match[2]));
        } else {
            urlParams[decode(match[1])] = decode(match[2]);
        }
    }
    return urlParams;
}

function removeCommas(nStr) {
    if (typeof (nStr) === 'number') {
        return nStr;
    }
    if (!nStr || !nStr.length) {
        return 0;
    }
    return parseFloat(nStr.replace(/,/g, ''));
}

function number_format (number, decimals, dec_point, thousands_sep) {
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}