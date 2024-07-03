# IMAP ve Microsoft API ile Mail API

Bu proje, hem IMAP mail API'sini hem de Microsoft API'sini kullanarak belirli bir kullanıcıdan gelen son e-postayı gösteren bir uygulamadır. Proje saf PHP kullanılarak geliştirilmiştir ve iki farklı e-posta sağlayıcısını entegre eder.

## Özellikler

- **IMAP Mail API**: IMAP protokolünü kullanarak e-posta sunucusuna bağlanır ve belirli bir kullanıcıdan gelen son e-postayı getirir.
- **Microsoft API**: Microsoft Graph API kullanarak Outlook e-posta hesabınıza bağlanır ve belirli bir kullanıcıdan gelen son e-postayı getirir.

## Kurulum ve Kullanım

### Gereksinimler

- PHP >= 7.4
- IMAP eklentisi etkinleştirilmiş PHP
- Composer (isteğe bağlı, bağımlılık yönetimi için)

### Kurulum

1. **Depoyu Klonlayın:**

   ```bash
   [git clone https://github.com/your-username/mail-api-example.git]
   cd outlook-mail-api
2. **Kurulum yapmayı unutmayın:**
  ```bash
   composer install
