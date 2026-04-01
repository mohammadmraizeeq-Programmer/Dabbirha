Here’s a cleaner, more professional, and industry-level version of your README. I removed the citation noise, improved wording, and made it look like something you’d confidently show to companies 👇

---

# **Dabbirha (دبّرها) – AI-Powered Home Services Platform**

**Dabbirha** is a full-stack web platform that connects homeowners with trusted service providers through an intelligent, user-friendly system. Developed as a graduation project, the platform integrates Artificial Intelligence to simplify issue diagnosis, service discovery, and booking.

---

## 🚀 **Key Features**

* **AI-Based Issue Diagnosis**
  Users can upload images of household problems (e.g., plumbing or electrical issues). The system leverages **Google Cloud Vision API** to analyze the image and automatically suggest the most relevant service category.

* **Location-Based Service Matching**
  Integrated with **Google Maps API**, the platform enables users to discover and connect with nearby service providers based on real-time location.

* **24/7 Smart Chat Support**
  A chatbot powered by **Google Dialogflow** provides automated customer assistance, improving user experience and reducing response time.

* **Secure Authentication & Payments**

  * **Google Authentication** for secure and seamless login
  * **PayPal API** integration for reliable and secure online transactions

* **Role-Based System Architecture**
  The platform includes dedicated dashboards and permissions for:

  * Customers
  * Service Providers
  * Administrators

---

## 🛠️ **Technology Stack**

* **Backend:**
  PHP (PDO & MySQLi) with a focus on secure database handling and structured architecture

* **Frontend:**
  Responsive, mobile-first UI built with **Bootstrap**, enhanced using **GSAP (GreenSock)**, **AOS (Animate On Scroll)** for smooth animations

* **APIs & Integrations:**

  * Google Cloud (Vision API, Maps API, Dialogflow)
  * PayPal API
  * Twilio API

* **Tools & Workflow:**

  * Composer (Dependency Management)
  * Git (Version Control)

---

## ⚙️ **Installation & Setup**

1. **Clone the Repository**

   ```bash
   git clone https://github.com/yourusername/Dabbirha.git
   cd Dabbirha
   ```

2. **Install Dependencies**
   Make sure Composer is installed, then run:

   ```bash
   composer install
   ```

3. **Configure Environment**

   * Navigate to `User/config/`
   * Rename `config.sample.php` to `config.php`
   * Add your database credentials and API keys

4. **Database Setup**

   * Import the provided `.sql` file into your MySQL server

---

## 🎓 **About the Developer**

**Mohammad Mraizeeq** – Full-Stack Developer based in Amman, Jordan

* **Education:**
  BSc in Computer Science, American University of Madaba (GPA: 80.4/100)

* **International Experience:**
  Completed a merit-based exchange semester at **Northern Arizona University, USA**

* **Technical Focus:**
  Building scalable web applications and integrating modern AI-driven solutions into real-world systems

---

## 📌 **Project Vision**

Dabbirha aims to modernize the home services industry by combining AI, automation, and intuitive design to create a seamless experience for both customers and service providers.

---
