
<div class="menu">

    <h2 class="py-[25px] text-center">Configuración</h2>

    <ul id="main-menu" class="main-menu space-y-2 ">
        <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-profile') }}">
            <li class="menu-element {{ $currentPage === 'myProfile' ? 'menu-element-selected' : '' }}">
                <div class="menu-content">
                    <div class="icon">{{ e_heroicon('user', 'outline', '#C7C7C7', 24, 24) }}</div>
                    <span>Mi perfil</span>
                </div>
            </li>
        </a>

        <li class="menu-element has-submenu">
            <div class="toggle-submenu menu-content {{ $currentPage === 'my_courses' ? 'menu-element-selected' : '' }}">
                <div>{{ e_heroicon('academic-cap', 'outline', '#C7C7C7', 24, 24) }}</div>
                <div class="icon"><span>Mis cursos</span></div>
                <div class="icon-up">{{ e_heroicon('chevron-up', 'outline', '#000000', 12, 12) }}</div>
                <div class="icon-down hidden">{{ e_heroicon('chevron-down', 'outline', '#000000', 12, 12) }}</div>
            </div>

            <ul class="sub-menu">
                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-courses-inscribed') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'inscribedCourses' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Inscritos</span>
                        </div>
                    </li>
                </a>

                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-courses-enrolled') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'enrolledCourses' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Matriculados</span>
                        </div>
                    </li>
                </a>

                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-courses-historic') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'historicCourses' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Histórico</span>
                        </div>
                    </li>
                </a>

            </ul>
        </li>

        <li class="menu-element has-submenu">
            <div class="toggle-submenu menu-content {{ $currentPage === 'my_courses' ? 'menu-element-selected' : '' }}">
                <div class="icon">{{ e_heroicon('academic-cap', 'outline', '#C7C7C7', 24, 24) }}</div>
                <div><span>Mis programas formativos</span></div>
                <div class="icon-up">{{ e_heroicon('chevron-up', 'outline', '#000000', 12, 12) }}</div>
                <div class="icon-down hidden">{{ e_heroicon('chevron-down', 'outline', '#000000', 12, 12) }}</div>

            </div>

            <ul class="sub-menu">
                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-educational-programs-inscribed') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'inscribedEducationalPrograms' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Inscritos</span>
                        </div>
                    </li>
                </a>

                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-educational-programs-enrolled') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'enrolledEducationalPrograms' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Matriculados</span>
                        </div>
                    </li>
                </a>

                <a aria-label="enlace" class="no-effect-hover" href="{{ route('my-educational-programs-historic') }}">
                    <li>
                        <div
                            class="menu-content sub-menu-content {{ $currentPage === 'historicEducationalPrograms' ? 'menu-element-selected' : '' }}">
                            {{ e_heroicon('square-2-stack', 'outline', '#C7C7C7', 24, 24) }}
                            <span>Histórico</span>
                        </div>
                    </li>
                </a>

            </ul>
        </li>

        <a aria-label="enlace" class="no-effect-hover" href="{{ route('competences-learning-results') }}">
            <li class="menu-element {{ $currentPage === 'competences_learning_results' ? 'menu-element-selected' : '' }}">
                <div class="menu-content">
                    <div class="icon">{{ e_heroicon('book-open', 'outline', '#C7C7C7', 24, 24) }}</div>
                    <span>Competencias y resultados de aprendizaje</span>
                </div>
            </li>
        </a>

        <a aria-label="enlace" class="no-effect-hover" href="{{ route('notifications') }}">
            <li class="menu-element {{ $currentPage === 'notifications' ? 'menu-element-selected' : '' }}">
                <div class="menu-content">
                    <div class="icon">{{ e_heroicon('bell-alert', 'outline', '#C7C7C7', 24, 24) }}</div>
                    <span>Notificaciones</span>
                </div>
            </li>
        </a>

        <a aria-label="enlace" class="no-effect-hover" href="{{ route('categories') }}">
            <li class="menu-element {{ $currentPage === 'categories' ? 'menu-element-selected' : '' }}">
                <div class="menu-content">
                    <div class="icon">{{ e_heroicon('rectangle-stack', 'outline', '#C7C7C7', 24, 24) }}</div>
                    <span>Mis categorías</span>
                </div>
            </li>
        </a>

    </ul>
</div>
