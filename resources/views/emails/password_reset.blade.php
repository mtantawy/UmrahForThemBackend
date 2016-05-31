<body>
    <p style="direction: rtl;">
        {{ trans('emails.greeting') }} {{ $name }},
        <br />
        <br />
        {{ trans('password_reset.body') }}
        <br />
        {{ $password }}
    </p>
</body>