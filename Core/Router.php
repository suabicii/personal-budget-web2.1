<?php

namespace Core;

class Router
{
    protected $routes = [];
    protected $params = [];

    /**
     * Dodaj trasę do tabeli routingu
     * 
     * @param string $route  URL trasy
     * @param array  $params  Parametry (kontroler, akcja etc.) - tablica asocjacyjna
     */
    public function add($route, $params = [])
    {
        // Konwersja trasy do wyrażenia regularnego
        $route = preg_replace('/\//', '\\/', $route);

        // Konwersja zmiennych, np. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Konwersja zmiennych niestandardowymi wyrażeniami regularnymi, np. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Dodanie organiczników początku i końca oraz flagę do nierozróżniania wielkości liter
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Pobierz wszystkie trasy z tabeli routingu
     * 
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Dopasuj trasę do tras z tabeli routingu, ustawiając właściwość zmiennej $params,
     * jeśli znaleziono trasę
     * 
     * @param string $url  URL trasy
     * 
     * @retrun boolean  true, jeśli znaleziono dopasowanie, false w przeciwnym wypadku
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Pobierz wartości grup przechwytywania
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Pobierz aktualnie dopasowane parametry
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * "Zdispatchuj" trasę (routing = pytania o kierunki; dispatching = podążanie tymi kierunkami), metoda tworzenia obiektu kontrolera i uruchamiania akcji
     * 
     * @param string $url  URL trasy
     * 
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if ($this->match($url)) {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) {
                    $controller_object->$action();
                } else {
                    throw new \Exception("Method $action in controller $controller cannot be called directly - remove the Action suffix to call this method");
                }
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Przekonwertuj string z myślnikami do StudlyCaps,
     * np. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Przekonwertuj string z myślnikami do notacji wielbłądziej (camelCase),
     * np. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Usuń zmienne query string z URL-a (jeśli jakieś wystąpią). Ponieważ dla trasy
     * używany jest pełny ciąg zapytania, wszelkie zmienne na końcu będą musiały
     * zostać usunięte zanim trasa zostanie dopasowana do tabeli routingu. Na
     * przykład:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * URL w formacie localhost/?page (jedna nazwa zmiennej, bez wartości)
     * jednak nie zadziała. (NB. plik .htaccess konwertuje pierwszy "?" do "&", gdy
     * jest przepuszczony przez zmienną $_SERVER).
     *
     * @param string $url  pełny URL
     *
     * @return string URL z usuniętym query string
     */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }

        return $url;
    }

    /**
     * Pobierz przestrzeń nazw do klasy kontrolera. Zdefiniowana przestrzeń nazw
     * w parametrze nazw jest dodana, jeśli istnieje.
     *
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
