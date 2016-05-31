<body>
    <p style="direction: rtl;">
        {{ trans('emails.greeting') }} {{ $performer_name }},
        <br />
        <br />
        {{ trans('umrah_stalled_cancellation_performer.body', ['deceased_name' => $deceased_name]) }}
    </p>
</body>