# Canela e Gengibre Theme Spec for Laravel 13 + Blade + Alpine.js + DaisyUI + Tailwind

Build a homepage template for a modern lifestyle / food editorial website called “Canela e Gengibre” using:

* Tailwind CSS
* DaisyUI components
* Alpine.js (light usage only)
* Clean HTML structure (ready for Laravel Blade integration)

---

## 🎯 Design Direction

The design must feel:

* editorial (like a modern food/lifestyle magazine)
* warm and natural (not corporate, not techy)
* calm, curated, and premium
* content-first (not UI-heavy or flashy)

Avoid:

* dashboard-style UI
* overly playful or trendy visuals
* pink, magenta, or bright saturated palettes

---

## 🎨 Color System (STRICT)

Use only this palette:

* cinnamon: #A85B32 (primary)
* ginger: #D98C3B (hover/accent)
* oat: #FDF7EF (soft background)
* moss: #54624E (dark accent)
* ink: #1F2937 (main text)
* sage / olive / sand (supporting tones)

Background:

* subtle warm gradient (off-white → warm beige)

---

## 🔤 Typography (VERY IMPORTANT)

Font pairing:

* Body → Inter
* Headings → Fraunces

Rules:

* Headings use Fraunces (hero, sections, featured titles)
* Body/UI uses Inter
* No extra fonts
* No decorative or trendy typography

Tone:

* lifestyle / editorial
* soft, human, readable

---

## 🧱 Layout Structure

### 1. Header

* sticky, translucent
* logo (icon + wordmark)
* navigation links
* search button + subscribe CTA

---

### 2. Hero (CRITICAL SECTION)

Two-column layout:

LEFT:

* large featured article
* full-image background
* dark gradient overlay
* text over image (bottom aligned)
* includes:

    * badge
    * large headline (Fraunces)
    * short description
    * CTA button

RIGHT:

* 4 stacked article cards
* compact layout
* no vertical stretching
* no empty gaps
* each card:

    * thumbnail
    * category
    * title
    * small description

Important:

* left and right columns must have same height
* use `gap-2`, NOT `justify-between`

---

### 3. Categories Section

Top:

* category pills (NO icons)
* rounded buttons
* active state in cinnamon

Below:

* content panel showing:

    * latest 4 articles from selected category
    * section title updates dynamically
    * “See all category” link

Interaction:

* use Alpine.js for:

    * active category state
    * content switching

---

### 4. Latest Articles Carousel

* horizontal scroll
* DaisyUI carousel
* compact cards
* smooth browsing feel

---

### 5. Featured Section

* left: large editorial block (gradient background)
* right: 2 supporting cards
* more emotional tone

---

### 6. About + Utility

Left:

* about content block

Right:

* search input
* tag pills

---

### 7. Newsletter

* dark gradient background (moss/ink tones)
* white text
* email input + CTA
* premium, calm feel

---

### 8. Footer

* simple
* navigation + social links
* clean and light

---

## 🧩 Component Style (DaisyUI)

Use DaisyUI for:

* buttons (`btn`)
* cards (`card`)
* inputs (`input`)
* badges (`badge`)
* carousel

BUT:

* override styles with Tailwind to avoid generic look
* keep rounded corners large
* use soft shadows

---

## 🚫 Non-Negotiables

* NO dashboard feel
* NO generic blog layout
* NO empty spacing in hero right column
* NO text below hero image (must overlay)

---

## Final Goal

The result should feel like:

👉 a curated food/lifestyle editorial homepage
👉 not a WordPress blog
👉 not a SaaS UI
👉 not a template demo

It should feel:

* calm
* intentional
* modern
* ready to become a real content brand

