<?php

class EmailTemplate {
    public static function getTemplate($content) {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Kenz Wheels</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #f6f9fc;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    color: #525f7f;
                    line-height: 1.6;
                }
                .email-wrapper {
                    max-width: 600px;
                    margin: 40px auto;
                    background-color: #ffffff;
                    border-radius: 4px;
                    padding: 40px;
                }
                .header {
                    margin-bottom: 30px;
                }
                .logo {
                    max-width: 120px;
                    height: auto;
                }
                .content {
                    color: #525f7f;
                    font-size: 15px;
                }
                .button {
                    display: inline-block;
                    background-color: #6772e5;
                    color: #ffffff;
                    padding: 12px 24px;
                    text-decoration: none;
                    border-radius: 4px;
                    margin: 20px 0;
                    font-weight: 600;
                }
                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #e6ebf1;
                    font-size: 13px;
                    color: #8898aa;
                }
                .social-links {
                    margin-top: 20px;
                }
                .social-links a {
                    display: inline-block;
                    margin-right: 15px;
                    color: #8898aa;
                    text-decoration: none;
                }
                .address {
                    margin-top: 20px;
                    font-size: 12px;
                }
                .bullet-point {
                    margin: 15px 0;
                    padding-left: 20px;
                    position: relative;
                }
                .bullet-point:before {
                    content: "•";
                    color: #6772e5;
                    position: absolute;
                    left: 0;
                }
                .unsubscribe {
                    color: #8898aa;
                    font-size: 12px;
                    text-decoration: none;
                }
                .unsubscribe:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="email-wrapper">
                <div class="header">
                    <img src="https://kenzwheels.com/marketplace/manage/assets/images/kenzwheels-light.png" alt="Kenz Wheels" class="logo">
                </div>
                
                <div class="content">
                    ' . $content . '
                </div>
                
                <div class="footer">
                    <div>—The Kenz Wheels team</div>
                    
                    <div class="address">
                        Kenz Wheels, Dubai, United Arab Emirates
                    </div>
                    
                    <div class="social-links">
                        <a href="https://facebook.com/kenzwheels">Facebook</a>
                        <a href="https://twitter.com/kenzwheels">Twitter</a>
                    </div>
                    
                    
                </div>
            </div>
        </body>
        </html>';
    }
} 