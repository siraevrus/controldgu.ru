<x-mail::message>
# Добро пожаловать в {{ $appName }}

Здравствуйте, {{ $userName }}!

Вам создана учётная запись. Пароль задал администратор — для входа используйте данные ниже. Храните письмо в надёжном месте.

**Email:** {{ $email }}

**Пароль:** `{{ $plainPassword }}`

<x-mail::button :url="$loginUrl">
Войти в систему
</x-mail::button>

С уважением,<br>
{{ $appName }}
</x-mail::message>
