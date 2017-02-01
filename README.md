# Paystack Gateway Module for WHMCS

A WHMCS module that allows users accept payments using Paystack

## Requirements

- Existing WHMCS installation on your web server.
- PHP (5.5.19 or more recent) and extensions, MySQL and web browser
- Supported Web Servers: Apache and Nginx
- cURL (7.34.0 or more recent)
- OpenSSL v1.0.1 or more recent

## Pre-Installation

- A paystack account is needed. Sign up at: 
[https://dashboard.paystack.co/#/signup][link-signup]. 

To receive live payments, you should request a Go-live after you are done with configuration and have successfully made a test payment.

## Installation
1. Copy [paystack.php](modules/gateways/paystack.php) in [modules/gateways](modules/gateways) to the `/modules/gateways/` folder of your WHMCS installation.

2. Copy [paystack.php](modules/gateways/callback/paystack.php) in [modules/gateways/callback](modules/gateways/callback) to the `/modules/gateways/callback` folder of your WHMCS installation.

## Post-Installation

- Request `Go-live` on the Paystack Dashboard.

## NB

- Paystack currently only accepts `NGN` for now.