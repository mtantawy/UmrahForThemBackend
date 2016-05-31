<body>
    <p style="direction: rtl;">
        {{ trans('emails.greeting') }} {{ $creator_name }}،
        <br />
        <br />
        {{ trans('umrah_stalled_cancellation_creator.body', ['deceased_name' => $deceased_name]) }}
    </p>
</body>