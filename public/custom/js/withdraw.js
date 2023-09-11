// $(document).ready(function () {
//
// });
validateForm();

function validateForm() {
    $('#form_info').validate({
        rules: {
            address: {
                required: true,
            },
            amount: {
                required: true,
                // digits: true,
            },
            password: {
                required: true,
            },
            chain: {
                required: true,
            },
        },
        messages: {
            address: {
                required: 'Please enter Recipient\'s address',
            },
            amount: {
                required: 'Please enter amount',
                // digits: 'Please enter number',
            },
            password: {
                required: 'Please enter password',
            },
            chain: {
                required: 'Please select transfer network',
            },
        }
    })
}
