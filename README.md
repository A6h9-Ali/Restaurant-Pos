Restaurant POS app:

🍽 Restaurant POS — SQLite Edition
What it is
A self-hosted, PHP-based Point of Sale system for restaurants. No cloud dependency — everything runs on the server with a local SQLite file as the database. No MySQL, no hosting setup beyond basic PHP.

Pages & Features
allOne-time wizard: sets base URL, restaurant name, language, currency, admin account, creates the SQLite DBLoginSecure login with hashed passwordsDashboardStats (today's revenue, orders, totals), 7-day chart, top selling items, recent ordersMenu ItemsAdd/edit/delete items with photo upload, Arabic+English names, price, active toggleNew Order (POS)Click-to-add menu grid, live order panel, quantity controls, discount (% or fixed), notes, place order, print receiptOrder HistoryFull order list, view receipt, edit any past order (items, quantities, discount, notes)SettingsRestaurant name, language, currency, colors, logo upload, change password

Technical Stack

PHP 7.4+ — no frameworks
SQLite via PDO — single .db file, zero server config
Bootstrap 5.3 — responsive UI
Chart.js — dashboard revenue chart

