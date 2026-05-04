<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Your Password</title>
    <style type="text/css">
        /* Client-specific Styles */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }

        table {
            border-collapse: collapse !important;
        }

        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background-color: #f4f7fa;
        }
    </style>
</head>

<body
    style="margin: 0; padding: 0; background-color: #f4f7fa; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center" style="padding: 40px 0 20px 0;">
                <img src="https://placehold.co/600x400?text=LOGO" alt="Company Logo"
                    width="160"
                    style="display: block; font-family: Arial, sans-serif; color: #1e293b; font-size: 24px; font-weight: bold;" />
            </td>
        </tr>
        <tr>
            <td align="center" style="padding: 0 10px 40px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="max-width: 500px; background-color: #ffffff; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #eef2f6;">
                    <tr>
                        <td align="left" style="padding: 40px 40px 20px 40px;">
                            <h1
                                style="margin: 0; color: #0f172a; font-size: 24px; font-weight: 800; line-height: 32px;">
                                Hello, {{ @$body['name'] }}! 👋</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="left"
                            style="padding: 0 40px 30px 40px; color: #64748b; font-size: 16px; line-height: 26px;">
                            We received a request to reset your password. Please use the following 5-digit security code
                            to verify your identity.
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 0 40px 30px 40px;">
                            <table border="0" cellpadding="0" cellspacing="10" width="100%">
                                <tr>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; width: 20%; height: 60px; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                        {{ @strval($body['message']['code'])[0] }}</td>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; width: 20%; height: 60px; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                        {{ @strval($body['message']['code'])[1] }}</td>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; width: 20%; height: 60px; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                        {{ @strval($body['message']['code'])[2] }}</td>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; width: 20%; height: 60px; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                        {{ @strval($body['message']['code'])[3] }}</td>
                                    <td align="center"
                                        style="background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; width: 20%; height: 60px; font-size: 28px; font-weight: bold; color: #4f46e5;">
                                        {{ @strval($body['message']['code'])[4] }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" bgcolor="#0f172a" style="border-radius: 16px;">
                                        <a href="{{env('APP_URL')}}" target="_blank"
                                            style="display: inline-block; padding: 18px 20px; font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; width: 100%;">Verify
                                            Account</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="left" bgcolor="#f1f5f9" style="padding: 20px 40px; border-radius: 0 0 24px 24px;">
                            <p style="margin: 0; font-size: 12px; color: #64748b; line-height: 18px;">
                                <strong>Security Tip:</strong> If you didn't request this code, someone may be trying to
                                access your account. Please log in and change your password immediately.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding: 0 10px 40px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
                    <tr>
                        <td align="center"
                            style="padding: 20px 0 0 0; color: #94a3b8; font-size: 12px; line-height: 18px; text-transform: uppercase; letter-spacing: 2px;">
                             All rights reserved. Copyright Ⓒ {{date('Y')}}<br />
                             {{env('APP_NAME')}}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0 0 0;">
                            <a href="#"
                                style="color: #4f46e5; text-decoration: none; font-size: 12px; font-weight: bold; margin: 0 10px;">Support</a>
                            <a href="#"
                                style="color: #4f46e5; text-decoration: none; font-size: 12px; font-weight: bold; margin: 0 10px;">Privacy
                                Policy</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
 