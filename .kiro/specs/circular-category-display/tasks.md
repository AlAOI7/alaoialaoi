# خطة التنفيذ - عرض الفئات بشكل دائري

## نظرة عامة

تحويل عرض صور الفئات من الشكل المربع/المستطيل إلى شكل دائري مع تقليل الأحجام وتحسين التخطيط، مع الحفاظ على جميع الوظائف الحالية.

## المهام

- [x] 1. تحديث عرض الفئات المميزة (featured-categories.php)
  - [x] 1.1 تطبيق الشكل الدائري على صور الفئات المميزة
    - تعديل CSS للصورة: تقليل الحجم من 100px إلى 90px
    - التأكد من تطبيق border-radius: 50%
    - إضافة border: 3px solid #fff
    - إضافة box-shadow للصورة
    - _المتطلبات: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [x] 1.2 تحديث تخطيط البطاقات والشبكة
    - تقليل padding البطاقة من 25px 15px إلى 20px 12px
    - تحديث grid-template-columns من minmax(180px, 1fr) إلى minmax(150px, 1fr)
    - تقليل gap من 25px إلى 20px
    - إضافة min-height: 180px للبطاقة
    - _المتطلبات: 3.3, 3.4_
  
  - [x] 1.3 تحسين التأثيرات التفاعلية
    - تطبيق transform: scale(1.15) على hover
    - تحديث box-shadow على hover
    - إضافة transition: all 0.3s ease
    - _المتطلبات: 4.1, 4.2, 4.3, 4.4_

- [ ] 2. تحديث عرض جميع الفئات (categories-section.php)
  - [x] 2.1 تطبيق الشكل الدائري على صور الفئات
    - تعديل .category-img: width و height إلى 120px
    - تطبيق border-radius: 50%
    - إضافة margin: 0 auto و display: block
    - إضافة border: 3px solid #fff
    - إضافة box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1)
    - _المتطلبات: 1.1, 1.2, 1.3, 1.4, 1.5_
  
  - [x] 2.2 تحديث حاوية الصورة
    - تغيير .category-img-container height من 180px إلى auto
    - إضافة padding: 20px
    - إضافة display: flex مع align-items و justify-content: center
    - إضافة background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%)
    - _المتطلبات: 2.1, 2.2, 2.3, 2.4_
  
  - [x] 2.3 تقليل حجم البطاقات
    - تقليل .category-item width من 280px إلى 240px
    - _المتطلبات: 3.1, 3.2_
  
  - [x] 2.4 تحسين التأثيرات التفاعلية
    - تطبيق transform: scale(1.15) rotate(5deg) على hover
    - تحديث box-shadow على hover
    - إضافة transition: all 0.3s ease
    - _المتطلبات: 4.1, 4.2, 4.3, 4.4_

- [ ] 3. Checkpoint - التحقق من التغييرات الأساسية
  - تأكد من أن جميع الصور تظهر بشكل دائري
  - تحقق من الأحجام الجديدة
  - تأكد من عمل التأثيرات على hover
  - اسأل المستخدم إذا كانت هناك أي أسئلة

- [x] 4. تحديث media queries للتوافق مع الأجهزة
  - [x] 4.1 تحديث breakpoint للأجهزة اللوحية (768px - 992px)
    - featured-categories: grid minmax(130px, 1fr)، gap: 18px
    - featured-category-img: 80px × 80px
    - category-item: width 220px
    - category-img: 110px × 110px
    - _المتطلبات: 5.2, 5.4_
  
  - [x] 4.2 تحديث breakpoint للأجهزة المحمولة (< 768px)
    - featured-categories: grid minmax(120px, 1fr)، gap: 15px
    - featured-category-img: 70px × 70px
    - featured-category padding: 15px 10px
    - category-item: width 200px
    - category-img: 100px × 100px
    - _المتطلبات: 5.3, 5.4, 5.5_
  
  - [x] 4.3 تحديث breakpoint للشاشات الصغيرة جداً (< 480px)
    - featured-categories: grid minmax(110px, 1fr)، gap: 12px
    - featured-category-img: 65px × 65px
    - category-item: width 180px
    - category-img: 90px × 90px
    - category-name: font-size 0.85rem
    - _المتطلبات: 5.3, 5.4, 5.5_

- [ ] 5. التحقق من الحفاظ على الوظائف الحالية
  - [ ] 5.1 التحقق من عمل الروابط والتنقل
    - تأكد من أن النقر على البطاقة ينقل إلى صفحة التفاصيل
    - تحقق من معاملات URL الصحيحة
    - _المتطلبات: 6.1, 4.5_
  
  - [ ] 5.2 التحقق من عمل AJAX وتحميل المنتجات
    - تأكد من عمل دالة loadCategoryProducts
    - تحقق من عرض المنتجات بشكل صحيح
    - _المتطلبات: 6.5_
  
  - [ ] 5.3 التحقق من عمل التمرير الأفقي
    - تأكد من عمل أزرار التمرير
    - تحقق من عمل التمرير بالماوس
    - تحقق من عمل التمرير باللمس على الأجهزة المحمولة
    - _المتطلبات: 6.6_
  
  - [ ] 5.4 التحقق من عرض الصورة الافتراضية
    - تأكد من عمل onerror handler
    - تحقق من عرض img/1.jpg عند فشل التحميل
    - _المتطلبات: 6.6, 7.5_
  
  - [ ] 5.5 التحقق من عرض الشارات والعدادات
    - تأكد من عرض featured badge للفئات المميزة
    - تحقق من عرض عدد المنتجات بشكل صحيح
    - _المتطلبات: 6.2, 6.3_

- [x] 6. إضافة تحسينات الأداء وإمكانية الوصول
  - [x] 6.1 إضافة تحسينات الأداء
    - إضافة will-change: transform للصور
    - إضافة @media (prefers-reduced-motion: reduce) لتقليل التأثيرات
    - _المتطلبات: 7.1, 7.2, 7.4_
  
  - [x] 6.2 تحسين إمكانية الوصول
    - التأكد من وجود alt text لجميع الصور
    - استخدام htmlspecialchars لأسماء الفئات في alt
    - _المتطلبات: 1.5, 2.5_
  
  - [x] 6.3 معالجة الأسماء الطويلة
    - إضافة text-overflow: ellipsis
    - إضافة -webkit-line-clamp: 2
    - إضافة max-height: 2.8em
    - _المتطلبات: 2.5_

- [ ] 7. Checkpoint النهائي - اختبار شامل
  - اختبر على متصفحات مختلفة (Chrome, Firefox, Safari, Edge)
  - اختبر على أجهزة مختلفة (Desktop, Tablet, Mobile)
  - تأكد من عمل جميع الوظائف بشكل صحيح
  - تحقق من الأداء وسرعة التحميل
  - اسأل المستخدم إذا كانت هناك أي ملاحظات أو تعديلات

## ملاحظات

- جميع التعديلات تتم على CSS فقط دون الحاجة لتعديل PHP
- الحفاظ على جميع الوظائف الحالية (AJAX، التنقل، التمرير)
- التركيز على التوافق مع جميع الأجهزة والمتصفحات
- استخدام object-fit: cover للحفاظ على جودة الصور
- كل مهمة تشير إلى المتطلبات المرتبطة بها للتتبع
