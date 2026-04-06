Done! Here’s how to access everything:

Access URLs
Role	URL	Credentials
Landing Page	http://localhost:8000/	-
Login	http://localhost:8000/login	-
Register	http://localhost:8000/register	-
Demo Accounts
Account	Email	Password	Access
Super Admin	admin@platform.com	password	http://localhost:8000/admin/tenants
Tenant (Kopi Senja)	hello@kopisenja.com	password	http://localhost:8000/dashboard/kopi-senja
Tenant (BajuKita)	hello@bajukita.com	password	http://localhost:8000/dashboard/bajukita
Demo Storefronts (Public)
http://localhost:8000/kopi-senja - Coffee shop
http://localhost:8000/bajukita - Fashion boutique
Flow
New Tenant Registration: Visit /register → Fill form → Store created (pending approval)
Tenant Login: Visit /login → Redirects to tenant dashboard
Super Admin: Visit /admin/tenants → Approve/suspend tenants
Customer: Visit /{store_link} → Browse products → Cart → WhatsApp checkout



# Clear browser cookies or use incognito

# Tenant login
http://localhost:8000/login
Email: hello@kopisenja.com
Password: password
→ Redirects to: /dashboard

# Super Admin login
http://localhost:8000/login
Email: admin@platform.com
Password: password
→ Redirects to: /admin/tenants

# Home page when logged in
http://localhost:8000/
→ Auto-redirects to appropriate dashboard