��    W      �     �      �     �     �     �  )   �  "     "   $  \   G  �   �  r   h	  p   �	  :   L
  B   �
  e   �
  6  0     g     v     �  	   �  �   �     8  v   @     �    �     �  0   
  _   ;     �  2   �  M   �     9  "   X  $   {  F   �  )   �  w     q   �  .   �  4   *  )   _  8   �  W   �  y     �   �  �   0  �   �  n   u  d   �  V   I     �  B   �  �   �  ,   �  e   �  5   '  A   ]     �  	   �  ?   �  W     \   ^  2   �  {   �     j  2   �  #   �     �  4   �  �    _   �  u   	  0    h   �  �     �   �  q   a   �   �   �   {!  t   "  -   y"  1   �"  V  �"  T   0$  @   �$     �$  	   �$     �$    �$  0   '     A'  +   Y'  K   �'  7   �'  /   	(  �   9(  ]  �(  �   $*  �   +  �   �+  |   V,  �   �,  8  ~-  )   �/     �/     0     0    .0     >1  �   U1  ?   (2  �  h2  3   ,4  [   `4  �   �4  4   w5  b   �5  �   6  6   �6  =   �6  @   -7  z   n7  J   �7  �   48  �    9  @   �9  U   ':  >   }:  ^   �:  �   ;  �   �;  	  �<  �   �=  K  �>  �   �?  �   �@  �   rA  (   -B  p   VB  �   �B  L   �C  �   �C  g   �D  u   +E  F   �E     �E  [   �E  �   ZF  �   G  j   �G  �   PH  0   I  d   BI  P   �I     �I  w   J  s  �J  �   �L  �   �M    |N  �   �P  =  <Q  "  zR  �   �S  '  wT  �   �U  �   ~V  T   eW  L   �W     X  �   Z  z   �Z     =[     J[  4   a[        <           F       I      E   @                     ?   %   &           R   P              C      .   0   '   ;   J           $   +       U      =   4              2   5             O          Q                         1   #      *       	   7             ,          3               V              8                  
              D   S   A         K   B          N      "   T   L   W      -   (   G             :      >   9           H   M           /   )   6   !         (see below for details). %1$s is %2$s of %3$s. (Number of relationships: %s) (that's overall almost as close as: %1$s) (that's overall as close as: %1$s) (that's overall closer than: %1$s) (that's overall not significantly closer than the closest relationship via common ancestors) A module providing various algorithms used to determine relationships. Includes a chart displaying relationships between two individuals, as a replacement for the original 'Relationships' module. A module providing various algorithms used to determine relationships. Includes an extended 'Relationships' chart. All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: Allow persistent toggle (user may show/hide relationships) And hopefully it shows how much better the new algorithm works ... Associated facts and events are displayed when the respective toggle checkbox is selected on the tab. Basically, each path (via common ancestors) between two individuals contributes to the CoR, inversely proportional to its length: Each path of length 2 (e.g. between full siblings) adds %1$s, each path of length 4 (e.g. between first cousins) adds %2$s, in general each path of length n adds (0.5)<sup>n</sup>. Chart Settings Common ancestor:  Common ancestors:  Debugging Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once. Display Displays additional relationship information via the extended 'Families' tab, and the extended 'Facts and Events' tab. Do not show any relationship Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs). Families Tab Settings Find a closest relationship via common ancestors Find a closest relationship via common ancestors, or fallback to the closest overall connection Find all overall connections Find all relationships via lowest common ancestors Find all smallest lowest common ancestors, show a closest connection for each Find other overall connections Find other/all overall connections Find the closest overall connections Find the closest overall connections (preferably via common ancestors) Finished - all link dates are up-to-date. For close relationships similar to the previous option, but faster. Internally just a combination of two other methods. For large trees, this process may initially take several minutes. You can always safely abort and continue later. How to determine relationships between parents How to determine relationships to associated persons How to determine relationships to spouses How to determine relationships to the default individual If this is unexpected, and there are recent changes, you may have to follow this link:  If this option is checked, relationships to the associated individuals are only shown for the following facts and events. If you do not want to change the functionality with respect to the original Facts and Events tab, select 'Do not show any relationship' in the first block. If you do not want to change the functionality with respect to the original Families tab, select 'Do not show any relationship' everywhere. If you do not want to use the chart functionality, hide this chart via Control Panel > Charts > %1$s Vesta Extended Relationships (note that the chart is listed under the module name). If you select this option everywhere, you should also disallow persistent toggle, as it has no visible effect. In particular if both lists are empty, relationships will never be shown for these facts and events. In that case, you should also disallow persistent toggle, as it has no visible effect. Individuals with Patriarchs Intended as a replacement for the original 'Relationships' module. It is more complicated to determine this exact CoR, and the differences are usually small anyway. Therefore, only the Uncorrected CoR is calculated. Legacy algorithm for Relationship path names Note that the facts and events to be displayed at all may be filtered via the preferences of the tab. Only show relationships for specific facts and events Options referring to overall connections established before %1$s. Options to show in the chart Patriarch Patriarchs are the male end-of-line ancestors ('Spitzenahnen'). Prefers partial paths via common ancestors, even if there is no direct common ancestor. Same option as in the original relationship chart, further configurable via recursion level: Same option as in the original relationship chart. Searching for regular overall connections would be pointless here because there is always a trivial HUSB - WIFE connection. Show common ancestors Show common ancestors on top of relationship paths Show legacy relationship path names Synchronization Synchronize trees to obtain dated relationship links The CoR (Coefficient of Relationship) is proportional to the number of genes that two individuals have in common as a result of their genetic relationship. Its calculation is based on Sewall Wright's method of path coefficients. Its value represents the proportion of genes held in common by two related individuals over and above those held in common by the whole population. More details here:  The following options refer to the same algorithms as used in the extended relationships chart: The following options use dated links, i.e. links that have been established before the date of the respective event. The process should be repeated (but will finish much faster) whenever a tree is edited, otherwise you may obtain inconsistent relationship data (displayed relationships are always valid, but they may not actually have been established at the given date, if changes in the tree are not synchronized here). The same information may be obtained via the branches list, where they show up as the heads of branches. Therefore, if one of the following options is selected, overall connections are determined via dated links, i.e. links that have been established before the date of the respective event. These relationships may only be calculated efficiently by preprocessing the tree data, via the synchronization link at the top of this page. This allows you to present meaningful connections, such as 'widowed husband marries the sister of his dead wife'. This list provides an overview by surname, and may be used to determine whether all individuals with a specific surname are actually descended from a common patriarch. This process calculates dates for all INDI - FAM links, so that relationships at a specific point in time can be calculated efficiently. This seems more useful in most cases (e.g. to determine the relationship to a godparent at the time of the baptism). Uncorrected CoR (Coefficient of Relationship) Uncorrected CoR (Coefficient of Relationship): %s Under normal circumstances the proportion of genes transmitted from ancestor to descendant &ndash; as estimated by Σ(0.5)<sup>n</sup> &ndash;  and the proportion of genes they hold in common (the true coefficient of relationship) are the same. If there has been any inbreeding, however, there may be a slight discrepancy, as explained here:  You can disable this via the module preferences, it's mainly intended for debugging. You may also adjust the access level of this part of the module. parents unlimited via legacy algorithm: %s Project-Id-Version: Ukrainian (Vesta Webtrees Custom Modules)
Report-Msgid-Bugs-To: 
PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE
Last-Translator: FULL NAME <EMAIL@ADDRESS>
Language-Team: Ukrainian <https://hosted.weblate.org/projects/vesta-webtrees-custom-modules/vesta-extended-relationships/uk/>
Language: uk
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;
X-Generator: Weblate 4.13-dev
  (докладніше дивись нижче). %1$s є %2$s до %3$s. (Кількість стосунків: %s) (це загалом майже так само близько, як: %1$s) (це загалом так близько, як: %1$s) (це загалом ближче ніж: %1$s) (це загалом незначно ближче, ніж найтісніші стосунки через спільних предків) Модуль надає різні алгоритми, що використовуються для визначення взаємозв’язків. Включає діаграму, що відображає стосунки між двома людьми, як заміну оригінальному модулю "Родинні зв'язки". Модуль надає різні алгоритми, що використовуються для визначення стосунків. Включає розширену діаграму "Родинні зв'язки". Усі шляхи між двома людьми, які додають свій внесок у CoR (Коефіцієнт взаємовідносин), як визначено тут: Дозволити постійне перемикання (користувач може показувати/приховувати стосунки) І, сподіваємось, це показує, наскільки краще працює новий алгоритм... Пов’язані факти та події відображаються коли на вкладці встановлено відповідний прапорець. В основному, кожен шлях (через спільних предків) між двома людьми вносить свій внесок у CoR, обернено пропорційний його довжині: Кожен шлях довжиною 2 (наприклад, між повними братами та сестрами) додає %1$s, кожен шлях довжиною 4 (наприклад, між першими кузенами) додає %2$s, загалом кожен шлях довжиною n додає (0,5)<sup>n</sup>. Налаштування діаграми Спільний предок:  Спільні предки:  Наладка Визначає найкоротший шлях між двома персонами через LCA (найнижчий загальний предок), тобто загальний предок, який з'являється на шляху лише один раз. Відображати Відображає додаткову інформацію про стосунки через розширену вкладку "Сім'ї" та розширену вкладку "Факти та події". Не демонструвати жодних стосунків Кожен SLCA (найменший найнижчий загальний предок) по суті являє собою частину дерева, якою обидві персони діляться (як частина своїх предків). Більш технічно, набір SLCA з двох осіб є підмножиною набору LCA (виключаючи всі LCA, які самі є предками інших LCA). Налаштування вкладки "Сім'ї" Знайти найближчі стосунки через спільних предків Знайти найближчі стосунки через спільних предків або повернутись до найближчого загального зв’язку Знайти усі загальні зв’язки Знайти усі стосунки через найнижчих спільних предків Знайти усіх найменших найнижчих спільних предків, показати найближчий зв’язок для кожного Знайти інші загальні зв’язки Знайти інші/всі загальні зв’язки Знайти найближчі загальні зв’язки Знайти найближчі загальні зв’язки (бажано через спільних предків) Готово - усі дати посилань є актуальними. Для близьких стосунків схожий на попередній варіант, але швидше. Внутрішньо лише поєднання двох інших методів. Для великих дерев цей процес спочатку може зайняти кілька хвилин. Ви завжди можете спокійно перервати та продовжити пізніше. Як визначити стосунки між батьками Як визначати стосунки з асоційованими особами Як визначати стосунки з подружжям Як визначити стосунки з індивідом за замовчуванням якщо це неочікувано, і є останні зміни, можливо, вам доведеться перейти за цим посиланням:  Якщо цей параметр позначений, стосунки з асоційованими персонами відображаються лише для наступних фактів та подій. Якщо ви не хочете змінювати функціональність щодо початкової вкладки "Факти та події", виберіть "Не показувати жодних стосунків" у першому блоці. Якщо ви не хочете змінювати функціональність щодо оригінальної вкладки "Сім'ї", виберіть скрізь "Не показувати жодних стосунків". Якщо ви не хочете використовувати функціонал діаграми, сховайте її за допомогою Панелі управління> Графіки>%1$s Vesta Extended Relationships (зверніть увагу, що графік вказаний під назвою модуля). Якщо ви обираєте цей параметр скрізь, вам також слід заборонити постійне перемикання, оскільки воно не має видимого ефекту. Зокрема, якщо обидва списки порожні, стосунки ніколи не відображатимуться для цих фактів та подій. У цьому випадку вам також слід заборонити постійне перемикання, оскільки воно не має видимого ефекту. Персони з патріархами Призначений як заміна оригінального модуля "Родинні зв'язки". Визначити цей точний CoR складніше, і різниці, як правило, невеликі. Отже, обчислюється лише невиправлений CoR. Спадковий алгоритм імен шляхів стосунків Зверніть увагу, що факти та події, які взагалі відображатимуться, можуть бути відфільтровані за допомогою налаштувань вкладки. Показувати стосунки лише для конкретних фактів та подій Параметри, що стосуються загальних з'єднань, встановлених до %1$s. Параметри для відображення на графіку Голова роду Патріархи - це предки по чоловічій лінії ("Spitzenahnen"). Віддає перевагу частковим шляхам через спільних предків, навіть якщо немає прямого спільного предка. Той самий варіант, що і в оригінальній діаграмі стосунків, додатково налаштовується за допомогою рівня рекурсії: Той самий варіант, що і в оригінальній діаграмі стосунків. Пошук звичайних загальних зв’язків тут не має сенсу, оскільки завжди існує тривіальне з’єднання HUSB - WIFE. Показати спільних предків Показати спільних предків на вершині шляхів відношень Показати назви шляхів застарілих стосунків Синхронізація Синхронізуйте дерева для отримання датованих зв'язків стосунків CoR (Коефіцієнт взаємозв'язку) пропорційний кількості генів, спільних у двох особин в результаті їх генетичного зв'язку. Його обчислення базується на методі коефіцієнтів шляху Сьюола Райта. Його значення представляє частку генів, що є спільними для двох споріднених індивідів, а також більше, ніж загальних для всієї популяції. Детальніше тут:  Наступні варіанти стосуються тих самих алгоритмів, що і в діаграмі розширених відносин: Наступні варіанти використовують датовані посилання, тобто посилання, які були встановлені до дати відповідної події. Процес слід повторювати (але закінчуватиметься набагато швидше) щоразу, коли дерево редагується, інакше ви можете отримати суперечливі дані (відображені стосунки завжди є дійсними, але вони можуть бути насправді не встановленими на вказану дату, якщо зміни в дереві тут не синхронізовані). Цю ж інформацію можна отримати через список філій, де вони відображаються як керівники філій. Отже, якщо обраний один із наступних варіантів, загальні зв’язки визначаються за датованими посиланнями, тобто посиланнями, які були встановлені до дати відповідної події. Ці відносини можуть бути ефективно розраховані лише шляхом попередньої обробки деревних даних за допомогою посилання для синхронізації вгорі цієї сторінки. Це дозволяє представити значущі зв’язки, наприклад, "чоловік овдовілий одружується з сестрою своєї померлої дружини". Цей список забезпечує огляд за прізвищами і його можна використовувати, щоб визначити, чи справді всі особи з певним прізвищем походять від спільного патріарха. Цей процес обчислює дати для всіх посилань INDI - FAM, так що стосунки в певний момент часу можуть бути ефективно розраховані. Це здається більш корисним у більшості випадків (наприклад, для визначення стосунків із хрещеним батьком на момент хрещення). Нескоригований CoR (коефіцієнт взаємовідносин) Невиправлений CoR (коефіцієнт стосунків): %s За звичайних обставин частка генів, що передаються від предка до нащадка - за оцінкою Σ (0,5)<sup>n</sup> - і частка генів, які вони мають у своєму розпорядженні (справжній коефіцієнт взаємозв'язку) однакові. Однак, якщо було інбридинг, може виникнути незначна розбіжність, як пояснено тут:  Ви можете вимкнути це за допомогою налаштувань модуля, він в основному призначений для налагодження. Ви також можете налаштувати рівень доступу до цієї частини модуля. батьки необмежений через застарілий алгоритм: %s 