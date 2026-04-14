# 🛡️ VGT Shield — Proof-of-Work Bot Defense for WordPress

[![License](https://img.shields.io/badge/License-AGPLv3-green?style=for-the-badge)](LICENSE)
[![Version](https://img.shields.io/badge/Version-2.0.0-brightgreen?style=for-the-badge)](#)
[![Platform](https://img.shields.io/badge/Platform-WordPress-21759B?style=for-the-badge&logo=wordpress)](#)
[![Algorithm](https://img.shields.io/badge/Algorithm-SHA--256_PoW-orange?style=for-the-badge)](#)
[![Status](https://img.shields.io/badge/Status-PLATIN-gold?style=for-the-badge)](#)
[![DSGVO](https://img.shields.io/badge/DSGVO-100%25_Konform-blue?style=for-the-badge)](#)
[![VGT](https://img.shields.io/badge/VGT-VisionGaia_Technology-red?style=for-the-badge)](https://visiongaiatechnology.de)

> *"No checkboxes. No Google. No compromise."*  
> *AGPLv3 — Für Menschen, nicht für Konzerne.*

---

## ⚠️ DISCLAIMER: EXPERIMENTAL R&D PROJECT

This project is a **Proof of Concept (PoC)** and part of ongoing research and development at
VisionGaia Technology. It is **not** a certified or production-ready product.

**Use at your own risk.** The software may contain security vulnerabilities, bugs, or
unexpected behavior. It may break your environment if misconfigured or used improperly.

**Do not deploy in critical production environments** unless you have thoroughly audited
the code and understand the implications. For enterprise-grade, verified protection,
we recommend established and officially certified solutions.

Found a vulnerability or have an improvement? **Open an issue or contact us.**


## 🔍 Was ist VGT Shield?

VGT Shield ist eine **hochperformante, DSGVO-konforme reCAPTCHA-Alternative für WordPress**. Das System eliminiert Bot-Interaktionen durch eine serverseitig validierte **Proof-of-Work (PoW) Engine**, die vollständig ohne Benutzerinteraktion und ohne externe Datentransfers (Zero-Cloud) operiert.

Kein Häkchen. Kein "Ich bin kein Roboter". Keine Google-Anfragen. Keine Cookies.  
Stattdessen: **unsichtbare, mathematische Bot-Abwehr direkt im Browser.**

<img width="1676" height="959" alt="VGT Shield" src="https://github.com/user-attachments/assets/28809c9b-cdd6-4430-8195-a329d852d127" />


```
Klassisches reCAPTCHA:
→ Nutzer klickt Checkbox
→ Google erhält Tracking-Daten
→ Drittanbieter-Cookies werden gesetzt
→ DSGVO-Probleme
→ Nutzer-Friction

VGT Shield:
→ Browser löst SHA-256 Challenge (Web Worker, isoliert)
→ Server validiert den Proof-of-Work
→ Keine externen Anfragen
→ Keine Cookies
→ Nutzer merkt nichts
```

---

## ⚡ Features

| Feature | Beschreibung |
|---|---|
| **Zero-UI Bot Defense** | Endbenutzer sehen keine Captchas oder Checkboxen — Sicherheit unsichtbar im Hintergrund |
| **SHA-256 PoW Engine** | Cryptografische Challenge-Response via Bitwise Hashing, isoliert im Web Worker |
| **100% DSGVO-konform** | Keine Drittanbieter-Anfragen, keine Cookies, kein Tracking |
| **Zero-Cloud** | Vollständig serverseitig — keine externen APIs, keine CDNs |
| **Replay Protection** | Jeder Hash einmalig gültig, TTL: 1800 Sekunden |
| **Deep Plugin Scanner** | AST-Regex-Parsing erkennt installierte Formularplugins und integriert sie automatisch |
| **Network Layer Hijacking** | Automatisches Abfangen von Netzwerk-Requests zur PoW-Header-Injektion |
| **<10ms Server-Validierung** | Minimale Latenz bei der serverseitigen Hash-Prüfung |
| **Dark/Light Mode** | Neural Aesthetics Admin-Dashboard mit vollständigem Theme-Support |

---

## 🏛️ Architektur

```
Client (Browser)
       │
       ▼
GET /wp-json/vgt-shield/v1/challenge
→ Server generiert kryptografische Challenge
       │
       ▼
Web Worker (isolierter Thread)
→ SHA-256 Bitwise Hashing
→ Mining der Lösung (keine UI-Blockierung)
       │
       ▼
POST Request (Formular / AJAX)
→ X-VGT-Shield-PoW Header injiziert
→ Server validiert Hash
→ Einmalige Nutzung (Replay Protection)
       │
       ▼
✅ Legitimer User → Formular wird verarbeitet
❌ Bot (kein gültiger PoW) → Request blockiert
```

---

## 🔌 Native Integrationen

| Kategorie | Plugins / Systeme |
|---|---|
| **E-Commerce** | WooCommerce (Login, Registrierung, Checkout) |
| **Formulare** | Contact Form 7, WPForms, Gravity Forms |
| **Community** | WordPress Core Kommentare |
| **Custom** | Beliebige Hooks via Admin-Interface scanbar und aktivierbar |

---

## 🚀 Installation

```bash
# 1. Plugin herunterladen oder klonen
cd /var/www/html/wp-content/plugins/
git clone https://github.com/visiongaiatechnology/antibotwp vgt-shield

# 2. In WordPress aktivieren
# Plugins → VGT Shield → Aktivieren

# 3. Konfiguration
# WordPress Admin → VGT Shield → Einstellungen
```

---

## ⚙️ Implementierung

### A. Global Infiltration (Empfohlen)

Im Standardmodus injiziert VGT Shield automatisch:
- **DOM-Listener** auf alle Formular-Submits
- **AJAX-Interceptors** für `Fetch` und `XMLHttpRequest`

Maximale Abdeckung ohne manuelle Konfiguration.

### B. Surgical Intervention (Shortcode)

Für gezielte Absicherung einzelner Seiten oder Formulare:

```
[vgt_shield]
```

Lädt die Shield-Engine und den Web-Worker-Kontext **nur auf der spezifischen Seite**. Ideal für hochsensible Landingpages oder individuelle Checkout-Flows.

---

## 📋 Systemanforderungen

| Komponente | Minimum |
|---|---|
| **WordPress** | 5.0+ |
| **PHP** | 7.4+ (empfohlen: 8.1+ für optimale JIT-Performance) |
| **WordPress REST API** | Aktiviert (Endpunkt: `/vgt-shield/v1/challenge`) |
| **Browser** | Web Worker Support (alle modernen Browser) |

> **Wichtig:** Die WordPress REST API muss aktiv sein. VGT Shield bezieht kryptografische Challenges über den Endpunkt `/vgt-shield/v1/challenge`. Ohne funktionierende REST-Schnittstelle schlägt der Handshake fehl.

---

## 🔐 Sicherheitsarchitektur

**Proof-of-Work Flow:**

1. **Challenge Generation** — Server generiert einmalige, zeitgebundene Challenge
2. **Client Mining** — Browser-Web-Worker löst SHA-256 Puzzle (CPU-Last außerhalb UI-Thread)
3. **Header Injection** — Gelöster PoW wird als `X-VGT-Shield-PoW` Header injiziert
4. **Server Validation** — Serverseitige Prüfung in <10ms
5. **Replay Protection** — Hash wird als verwendet markiert (TTL: 1800s), Memory-Cache (`wp_cache`), Fallback auf Transients

**Warum PoW Bot-sicher ist:**

Bots können SHA-256-Challenges nicht in Echtzeit lösen ohne erheblichen Rechenaufwand — das macht automatisierte Massenabfragen wirtschaftlich unrentabel, während legitime Browser-Nutzer den Prozess im Hintergrund lösen ohne es zu bemerken.

---

## 🆚 VGT Shield vs. reCAPTCHA

| Kriterium | VGT Shield | Google reCAPTCHA |
|---|---|---|
| **DSGVO-Konformität** | ✅ 100% | ❌ Problematisch |
| **Externe Anfragen** | ✅ Keine | ❌ Google-Server |
| **Cookies** | ✅ Keine | ❌ Tracking-Cookies |
| **Nutzer-Friction** | ✅ Zero-UI | ❌ Checkbox / Bilderrätsel |
| **Datensouveränität** | ✅ 100% lokal | ❌ Google-abhängig |
| **Open Source** | ✅ AGPLv3 | ❌ Proprietär |
| **Hosting-Kosten** | ✅ Keine API-Kosten | ⚠️ Ab bestimmtem Volumen |

---

## 💰 Support the Project

[![Donate via PayPal](https://img.shields.io/badge/Donate-PayPal-00457C?style=for-the-badge&logo=paypal)](https://www.paypal.com/paypalme/dergoldenelotus)

| Methode | Adresse |
|---|---|
| **PayPal** | [paypal.me/dergoldenelotus](https://www.paypal.com/paypalme/dergoldenelotus) |
| **Bitcoin** | `bc1q3ue5gq822tddmkdrek79adlkm36fatat3lz0dm` |
| **ETH** | `0xD37DEfb09e07bD775EaaE9ccDaFE3a5b2348Fe85` |
| **USDT (ERC-20)** | `0xD37DEfb09e07bD775EaaE9ccDaFE3a5b2348Fe85` |

---

## 🔗 VGT Ecosystem

| Tool | Typ | Zweck |
|---|---|---|
| 🛡️ **VGT Shield** | **Bot Defense** | Zero-UI PoW reCAPTCHA-Alternative für WordPress |
| ⚔️ **[VGT Auto-Punisher](https://github.com/visiongaiatechnology/vgt-auto-punisher)** | **IDS** | L4+L7 Hybrid IDS — Angreifer werden terminiert bevor sie anklopfen |
| 📊 **[VGT Dattrack](https://github.com/visiongaiatechnology/dattrack)** | **Analytics** | Sovereign Analytics Engine — deine Daten, dein Server |
| 🔐 **[VGT Myrmidon](https://github.com/visiongaiatechnology/vgtmyrmidon)** | **ZTNA** | Zero Trust Network Access für WordPress |
| 🌐 **[VGT Global Threat Sync](https://github.com/visiongaiatechnology/vgt-global-threat-sync)** | **Preventive** | Täglicher Threat Feed — bekannte Angreifer blockieren bevor sie ankommen |
| 🔥 **[VGT Windows Firewall Burner](https://github.com/visiongaiatechnology/vgt-windows-burner)** | **Windows** | 280.000+ APT-IPs in der nativen Windows Firewall |

---

## 🤝 Contributing

Pull Requests willkommen. Für größere Änderungen bitte zuerst ein Issue öffnen.

Lizenziert unter **AGPLv3** — *"Für Menschen, nicht für SaaS-Konzerne."*

---

## 🏢 Built by VisionGaia Technology

[![VGT](https://img.shields.io/badge/VGT-VisionGaia_Technology-red?style=for-the-badge)](https://visiongaiatechnology.de)

VisionGaia Technology entwickelt enterprise-grade Security-Infrastruktur — engineered nach dem DIAMANT VGT SUPREME Standard.

> *"reCAPTCHA gibt deine Nutzerdaten an Google weiter. VGT Shield gibt sie niemandem."*

---

*Version 2.0.0 — VGT Shield // Zero-UI Bot Defense // SHA-256 PoW // AGPLv3*
