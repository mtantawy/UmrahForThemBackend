<body>
    <p style="direction: rtl;">
        {{ trans('emails.greeting') }} {{ $creator_name }}،
        <br />
        <br />
        {{ trans('umrah_status_update.body', ['deceased_name' => $deceased_name, 'umrah_status' => $umrah_status]) }}
    </p>
</body>