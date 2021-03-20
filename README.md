# UniqueProcess

## Описание

Класс создан для того, чтобы ограничить количество запускаемых экземпляров скрипта только одним.
**Внимание!** класс использует в работе функцию  [posix_kill()](https://www.php.net/manual/function.posix-kill.php), что ограничивает использование пакета только POSIX системами. 

## Использование

При вызове Вашего скрипта нужно создать экземпляр класса, передав ему адрес файла, куда будет записан pid процесса.
Сам файл может не существовать. Главное, чтобы существовала и была доступна для записи директория.

Далее делаем попытку пометить процесс начатым.

Метод ```UniqueProcess::markProcessStarted()``` вернет ```true``` в случае успешного создания файла pid либо ```false```, в случае проблем с созданием такого файла либо если процесс уже запущен.
Даже в случае, если pid-файл не удален, но процесс завершился, метод вернет ```true```.

Метод ```UniqueProcess::markProcessEnded()``` удаляет pid-файл. После удаления другой процесс может стартовать. 

```php
use web136\one_process\UniqueProcess;

$pid = __DIR__ . '/pid/pid_file.pid';

try{

$UniqueProcess = new UniqueProcess($pid);

if ($UniqueProcess->markProcessStarted()) {

    // Some your actions
    
    $UniqueProcess->markProcessEnded();
}
else{
    echo "Сообщение о том, что процесс уже запущен \n";
}


}
catch(Exception $exception){
    // Handle exception
}
```