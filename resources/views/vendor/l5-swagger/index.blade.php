<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>{{ $documentation }}</title>

    <link rel="icon" type="image/png"
          href="{{ asset('vendor/l5-swagger/images/favicon-32x32.png') }}"
          sizes="32x32"/>

    <link rel="icon" type="image/png"
          href="{{ asset('vendor/l5-swagger/images/favicon-16x16.png') }}"
          sizes="16x16"/>

    <link href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}"
          rel="stylesheet"
          type="text/css"/>

    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }

        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>

<body>

<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>

<script>
    window.onload = function () {

        const urls = [];

        @foreach($urlsToDocs as $title => $url)
            urls.push({
                name: "{{ $title }}",
                url: "{{ $url }}"
            });
        @endforeach

        const ui = SwaggerUIBundle({

            dom_id: '#swagger-ui',

            url: "{!! $urlToDocs !!}",

            /**
             * Custom Swagger Tag order
             */
          tagsSorter: function (a, b) {
    const order = {
        'Authentication': 1,
        'Auth': 1,
        'CSRF': 2,
        'Provider Profile': 3,
        'Provider': 3,
        'Availability': 4,
        'Locations': 5,
        'Categories': 6,
        'Services': 7,
        'Working Hours': 8,
        'Schedule Exceptions': 9,
        'Public Holidays': 10,
        'Bookings': 11,
        'Payments': 12,
        'Admin - Users': 13,
        'Admin - Audit Logs': 14,
        'Admin - Reports': 15,
        'Admin - Locations': 16,
        'Admin - Services': 17,
        'Health': 18
    };

    const orderA = order[a] ?? 999;
    const orderB = order[b] ?? 999;

    if (orderA !== orderB) {
        return orderA - orderB;
    }

    return a.localeCompare(b);
},


            /**
             * Keep L5-Swagger operation sorting configuration
             */
            operationsSorter: {!! isset($operationsSorter)
                ? '"' . $operationsSorter . '"'
                : 'null'
            !!},

            configUrl: {!! isset($configUrl)
                ? '"' . $configUrl . '"'
                : 'null'
            !!},

            validatorUrl: {!! isset($validatorUrl)
                ? '"' . $validatorUrl . '"'
                : 'null'
            !!},

            oauth2RedirectUrl:
                "{{ route(
                    'l5-swagger.'.$documentation.'.oauth2_callback',
                    [],
                    $useAbsolutePath
                ) }}",

            /**
             * Add CSRF token to requests
             */
            requestInterceptor: function (request) {

                const csrfCookie = document.cookie
                    .split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='));

                if (csrfCookie) {

                    const csrfToken = decodeURIComponent(
                        csrfCookie.split('=')[1]
                    );

                    request.headers['X-CSRF-TOKEN'] = csrfToken;
                }

                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",

            docExpansion:
                "{!! config(
                    'l5-swagger.defaults.ui.display.doc_expansion',
                    'none'
                ) !!}",

            deepLinking: true,

            filter:
                {!! config(
                    'l5-swagger.defaults.ui.display.filter'
                ) ? 'true' : 'false' !!},

            persistAuthorization:
                "{!! config(
                    'l5-swagger.defaults.ui.authorization.persist_authorization'
                ) ? 'true' : 'false' !!}",
        });

        window.ui = ui;
    };
</script>

</body>
</html>