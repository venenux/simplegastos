#!/bin/bash

# este archivo crea un usuario en jabberd, shadow y owncloud, 
# adicional en el systema, si roundcube y correo imap usa pam como login en el correo
# dado el courier en venenux por defecto usa pam lo crea en los tres
# primer parametro es id usuario segundo es clave tercero es el dominio 
# dominio se asume es el mismo sin subpartes ni especiales para cada sistema
# para efectos de desarrollo el dominio no necesita ser igual, 
# simplemente debe ser el domain del jabber ejemplo xx@localhost
# en caso ejabberd sea instlado y usado con la config por defecto de debian
# el cuarto parametro es el comando (cambiarclave, nuevousuario, cuotamail, cuotanube, cuotaftp)

#TODO desde variable 1 a 4 verificar solo letras y numeros
#TODO desde variable 5 verificar solo algunas palabras, esto evita hackeo

#rutas bases
archivosconfig=/home/intranet/config
archivolog=/var/log/adminusersnew.log
# configuracion por defecto
archivogroups=$archivosconfig/.gruposorganizacion
quota=1024768000
adminusersys=systemas
adminusercla=systemas.1.com.net.ve
elsoporteh=37.10.252.96
elsoporteu=simpleticket
elsoportedb=simpleticket
elsoportec=simpleticket.1.com.net
elproyectoh=10.10.34.20
elproyectou=redmine
elproyectodb=redmine_default
elproyectoc=redmine.1.com
elproyectohg=10.10.34.20
elproyectoug=gastossystema
elproyectodbg=gastossystema
elproyectocg=gastossystema.1
dominio="intranet1.net.ve"
dominio2="10.10.34.22"
grupo="999-todos"
sufix=S

ayuda()
{
    echo "Forma de uso:"
    echo ""
    echo "$@ opcion <parametro>"
      echo -e "\t-u <username>"
      echo -e "\t-c <userpass>"
      echo -e "\t-g <departamento>"
      echo -e "\t-o <ordenejecutar> (crear/clave/borrar/tamanio/desactivar)"
      echo -e "\t-t <tamaño> tamano en bytes de la cuota de correo o fichero"
      echo -e "\t[-d <dominio/pagina>]"
      echo -e "\t[-a <adminoc>] (requerido a veces su usuario administrativo)"
      echo -e "\t[-k <adminpass>] (requerido a veces su usuario administrativo)"
      echo -e "\t-h muestra el texto de ayuda"
        echo "el nombre de usuario debe ser minusculas, clave solo numeros y letras"
        echo "EL GRUPO DEBE EXISTIR, sino el script falla, el grupo se verifica en config."
      echo -E ""
}

while getopts "u:c:d:g:o:t:v:a:k:h" opt; do
  case $opt in
    d)	# dominio/url al cual se esta manejando (usualmente seteado a configuracion)  
		dominio=$OPTARG
      ;;
    u)    # usuario  
		username=$OPTARG
      ;;
    c)    # clave usuario
		claveuse=$OPTARG
      ;;
    g)    # departamento sera grupo usuario
		grupo=$OPTARG
      ;;
    t)    # quota de owncloud/elfichero o courier/elcorreo  
		quota=$OPTARG
      ;;
    o)    # comando a ejecutar puede ser crear, cambiarclave, reset, tamaniom, tamanios, tamaniof, borrar
		comando=$OPTARG
      ;;
    a)
		adminusersys=$OPTARG
		;;
    k)
		adminusercla=$OPTARG
		;;
    h)
		echo "Interfaz para user+pam+courier+ejabberd+owncloud+ftp"
		echo ".. gestiona un usuario en estos siempre y cuando pam sea para courier y ftp"
		echo ".. por ahora ejabberd, owncloud y ftp deben correr en el mismo servidor"
			ayuda
		exit 0
      ;;
    *)
		ayuda
		echo "Error: falto un argumento, opcion invalida o no uso correctamente el comando"
		echo "los archivos de configuracion se revisan por dominio, grupos y clave administrador"
		echo "Error en adminusers $comando con parametro no reconocido, usuario que ejecuta es $adminusersys " | mail -s "Error sysnet adminusers: 1" postmaster
		exit 1
      ;;
  esac
done

# usuario y claves son mandatorios
if [ "$username" == "" -o "$comando" == "" ]; then
    echo " USUARIO Y COMANDO SE REQUIERE!"
    ayuda
    echo -e "... EL USUARIO Y EL COMANDO/ORDEN ES NECESARIO SIEMPRE,\n LO USASTE MAL, pasa el usuario y la clave"
	echo "Error en adminusers $comando falto el usuario o la clave, usuario que ejecuta es $adminusersys " | mail -s "Error sysnet adminusers: 2" postmaster
    exit 2
fi
# usuario y grupo ni vacio ni reservado
if [ "$grupo" == "" -o "$username" == "$grupo" ]; then
    grupo="999-todos"
    ayuda
    echo -E "error: debe especificar el grupo, e.g. \"999-todos\" no es valido, pues ya se asume"
	echo "Error debe especificar grupo, usuario que ejecuta es $adminusersys" | mail -s "Error sysnet adminusers: 4" postmaster
    exit 4
fi
# dominio debe ser valido
if [ "$dominio" == "" ]; then
    dominio=localhost
    ayuda
    echo "error: debe especificar el dominio"
	echo "Error debe especificar dominio, usuario que ejecuta es $adminusersys" | mail -s "Error sysnet adminusers: 3" postmaster
    exit 3
fi
# obviamente el comando (orden) que queire hacer
if [ "$comando" == "" -o "$comando" == "help" ]; then
    ayuda
    messageerror="error: debe especificar el comando de la accion sobre ese usuario.."
    echo $messageerror
	echo $messageerror | mail -s "Error sysnet adminusers: 3" postmaster
    exit 5
fi
# revisar si esta el programa para enviar correos
if [ ! -x /usr/bin/mail ]; then
    echo "bsd-mailx package its not installed"
    exit 1
fi
# revisar si esta el programa para consultar y mostrar grupos awk
if [ ! -x /usr/bin/awk ]; then
    echo "gawk package its not installed"
    exit 1
fi
# revisar si esta el programa para consultar mysql
if [ ! -x /usr/bin/mysql ]; then
    echo "mariadb-client package its not installed"
    exit 1
fi
# revisar si no esta el archivo de grupos, sino crearlo con grupos minimos
if [ ! -e "$archivogroups" ] ; then
touch $archivogroups
cat <<EOT >> $archivogroups
000-systemas
111-presidencia
112-aduanasvnz
113-impuestosvnz
121-bancosvnz
122-tesoreriavnz
131-nominavnz
132-rrhhvnz
134-coordinacionvnz
136-rrhhvnz
138-mantenimientovnz
142-inventariovnz
151-vigilanciavnz
153-seguridadvnz
157-mercanciasvnz
161-soportevnz
161-soporterep
163-contabilidadvnz
172-correspondenciavnz
173-serviciosbasicos
174-serviciosgenerales
181-importacionvnz
195-analisisvnz
202-showroomropavnz
203-showroomhogarvnz
204-gastosvnz
205-supervisionvnz
206-auditoriavnz
208-visualvnz
210-administracionvnz
220-comercializacionvnz
245-operacionesvnz
301-publicidadvnz
400-tiendascapital
500-tiendascentro
600-tiendasfalconzulia
700-tiendasoriente
800-tiendasandes
900-tiendascentrooccidente
999-encargados
999-galpones
999-tiendas-and
999-tiendas-cap
999-tiendas-cen
999-tiendas-coc
999-tiendas-csr
999-tiendas-faz
999-tiendas-ori
999-tiendas-pan
999-tiendas-pur
999-todos
EOT
fi
errore=$?

# --------- funcionaes de validacion de sistema venenux colab ----------

# revisar si el grupo es valido usando el archivo
revisargrupos()
{
	set -f
IFS='
'
	for line in $(cat "$archivogroups"); do 
		if [ "$grupo" != "$line" ]; then
			gruporevisar="999-todos"
		else
			gruporevisar=$line
			break
		fi
	done
	set +f
	if [ "$gruporevisar" == "999-todos" ]; then
		ayuda
		echo "ESTOS SON LOS GRUPOS VALIDOS"
		cat $archivogroups | awk '{print}' ORS=' - '
		echo ""
		echo -E "error: $grupo no es valido, use alguno de los mostrados"
        	echo "Error el grupo $grupo no esta permitido, debe ser valido como los del chat, usuario que ejecuta es $adminusersys" | mail -s "Error sysnet adminusers: 4" postmaster
		exit 4
	fi
}

# establecer un mecanismo de respuesta a los errores del usuario
respuestasalir()
{
	lasted=$? # must store, due if next command are performed now value are zero (the echo)
	if [ "$lasted" != "0" ]; then
	    errore=$(($errore + 1))
	fi
}

usercorreo=$username@$dominio
# crear el usuario con esta funcion
procesar_usuario()
{
    revisargrupos
	#temporalosticket 
    # crear el grupo al cual pertenece el usuario sin crear el suyo
    existe=$(grep -E -i -w "$grupo" /etc/group 2>/dev/null);
    if [ "x$existe" == "x" ]; then
        groupadd -f $grupo
    fi
    respuestasalir
    echo "paso 1: grupo $grupo: $existe para incluirle $username , cantidad errores: $errore" >> $archivolog

    # added to system, courier are configure to use systems users
    existe=$(grep -E -i -w "$username" /etc/passwd 2>/dev/null)
    if [ "x$existe" == "x" ]; then
        useradd -c $username -d /home/$username -k /etc/skel -m -U -G $grupo -s /bin/false $username
    fi
    respuestasalir
    echo "paso 2: usuaro $username:$claveuse $existe del OS (pam) cantidad errores: $errore" >> $archivolog

    # asignar la clave delusuario en el sistema operativo, todo login via pam se sincroniza
    echo $username:$claveuse | chpasswd;
    respuestasalir
    echo "paso 3: clave $username:$claveuse $existe del OS (pam) cantidad errores: $errore" >> $archivolog

    # crear en los proxies de los sistemas remotos ocultos, para que se vean por medio de usuario y clave
    touch $archivosconfig/.webproyectsaccess;touch $archivosconfig/.webarchivosaccess;touch $archivosconfig/.webreportesaccess
    htpasswd -b $archivosconfig/.webproyectsaccess $username $claveuse > /dev/null 2>&1
    if [ "$gruporevisar" == "000-systemas" ]; then
        htpasswd -b $archivosconfig/.webarchivosaccess $username $claveuse > /dev/null 2>&1
    fi
    if [[ $gruporevisar == *"gasto"* ]]; then
        htpasswd -b $archivosconfig/.wereportesaccess $username $claveuse > /dev/null 2>&1
    fi
    # crea en el sistema de gasto pero nolo habilita, debe ser manual por el mismo departamento
    if [[ $gruporevisar == *"gasto"* ]]; then
        mysql -u $elproyectoug -p$elproyectocg -h $elproyectohg -e "INSERT INTO gastossystema.usuarios ( intranet, clave, nombre, estado, acc_lectura, acc_escribe, acc_modifi, sessionficha) VALUES ( '$username', '$claveuse', '$username', 'ACTIVO', 'TODO', 'TODO', 'TODO', 'systema');" $elproyectodbg >/dev/null 2>&1
    else
        if [[ $gruporevisar == *"presidencia"* ]]; then
            mysql -u $elproyectoug -p$elproyectocg -h $elproyectohg -e "INSERT INTO gastossystema.usuarios ( intranet, clave, nombre, estado, acc_lectura, acc_escribe, acc_modifi, sessionficha) VALUES ( '$username', '$claveuse', '$username', 'ACTIVO', 'TODO', 'TODO', 'TODO', 'systema');" $elproyectodbg >/dev/null 2>&1
        else
            mysql -u $elproyectoug -p$elproyectocg -h $elproyectohg -e "INSERT INTO gastossystema.usuarios ( intranet, clave, nombre, estado, acc_lectura, acc_escribe, acc_modifi, sessionficha) VALUES ( '$username', '$claveuse', '$username', 'INACTIVO', 'NADA', 'NADA', 'NADA', 'systema');" $elproyectodbg >/dev/null 2>&1
        fi
    fi
    echo "paso 4: accesoweb I/O conflict, si no accede cambiar la clave $username errores:$errore " >> $archivolog

    echo "parte 1: $username:$claveuse $existe en pam+web+passwd, errores: $errore"

    # crear en el osticket
    temporalosticket

    echo "paso 5: osticket $errore creado solo usuario cliente, no analista staff" >> $archivolog
    # added to chat, ejabberd, thought ejabberdctl commandline
    ejabberdctl --auth $adminusersys $dominio $adminusercla  check_account $username $dominio 2>/dev/null
    existe=$?
    if [ "$existe" != "0" ]; then
        ejabberdctl --node ejabberd --auth $adminusersys $dominio $adminusercla register $username $dominio $claveuse > /dev/null 2>&1
    fi
    respuestasalir
    echo "paso 6: chat verificado $username: $existe cantidad errores: $errore" >> $archivolog
        # asignar la clave en el chat ejabberd
        ejabberdctl --node ejabberd --auth $adminusersys $dominio $adminusercla change_password $username $dominio $claveuse
        respuestasalir
    echo "paso 7: clave $username:$claveuse $existe del chat errores: $errore" >> $archivolog
        # asignar el grupo en ejabberd, si no existe se crea, si existe no hay problema
        ejabberdctl --node ejabberd --auth $adminusersys $dominio $adminusercla srg_create $grupo $dominio $grupo "Grupo de $grupo" "$grupo"
        respuestasalir
    echo "paso 8: grupo en chat habilitado $grupo errores: $errore" >> $archivolog
        # agregar al grupo asignado,verifica si existe en grupo
#        existe=$(ejabberdctl  --node ejabberd --auth $adminusersys $dominio $adminusercla srg_get_members $grupo $dominio | grep $username);
        existe=$(ejabberdctl --auth $adminusersys $dominio $adminusercla  srg_get_members $grupo $dominio | grep $username);
        if [ "x$existe" == "x"  ]; then
            ejabberdctl --auth $adminusersys $dominio $adminusercla  srg_user_add $username $dominio $grupo $dominio
        fi
        respuestasalir
    echo "paso 8: chat $username en $grupo errores: $errore" >> $archivolog

    echo "parte 2 $username:$grupo $existe en pam+chat+group, errores: $errore"

        # added to owncloud instance, no cares if are in another host (requires the patched restapi from simplemail)
        curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users -s -d usernid="$username" -d passwordn="$claveuse" --user "$adminusersys:$adminusercla" -XPOST -k >/dev/null
        respuestasalir
        curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d email="$username@$dominio" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
        respuestasalir
        curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d password=$claveuse --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
        respuestasalir
        echo "paso 9: añadido a nubeweb owncloud: errores $errore" >> $archivolog

    echo "parte 3 $username:$grupo $existe en owncloud+nubeweb, errores: $errore"

        # asignando carpetade de correo y cuota
        maildirmake /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Trash /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Junk /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Sent /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Archive /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Drafts /home/$username/Maildir > /dev/null 2>&1
        maildirmake -f Confirms /home/$username/Maildir > /dev/null 2>&1
        echo "INBOX.Sent" > /home/$username/Maildir/courierimapsubscribed
	respuestasalir
        echo "INBOX.Drafts" >> /home/$username/Maildir/courierimapsubscribed
        echo "INBOX.Junk" >> /home/$username/Maildir/courierimapsubscribed
        echo "INBOX.Trash" >> /home/$username/Maildir/courierimapsubscribed
        echo "INBOX.Archive" >> /home/$username/Maildir/courierimapsubscribed
        echo "INBOX.Confirms" >> /home/$username/Maildir/courierimapsubscribed
        respuestasalir
        maildirmake -q $quota$sufix /home/$username/Maildir
        curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -XPUT -k -d quota=$quota -d userid=$username >/dev/null 2>&1
        chown -R $username:$username /home/$username
        echo "procesar $username en home privado definicion de login pam errores: $errore" >> $archivolog
        echo "parte 4 $username en home privado definicion de login pam errores: $errore"
        respuestasalir
        echo "paso 10: configurado home $existe errores: $errore" >> $archivolog
        respuestasalir
        # afinando sus permisos 
        echo "procesado :$username|$claveuse en $dominio; errores: $errore"
        respuestasalir
}

# destruir el usuario con esta funcion
destruir_usuario()
{

    existe=$(grep -E -i -w "$username" /etc/passwd 2>/dev/null)
    if [ "x$existe" != "x" ]; then
#	deluser --force --remove-home $username
	userdel -f -r $username
    fi
    respuestasalir
    echo "paso 1: $username: pam+passwd, $existe ; errores: $errore" >> $archivolog
    echo "paso 1: $username: pam+passwd, $existe ; errores: $errore"
    if [ "$grupo" != "$username" ]; then
        groupdel $username > /dev/null 2>&1
    fi
    echo "paso 2: eliminado del $grupo $existe y errores: $errore" >> $archivolog
    echo "paso 2: eliminado del $grupo $existe y errores: $errore"
    # crear en los proxies de los sistemas remotos ocultos, para que se vean por medio de usuario y clave
    htpasswd -D $archivosconfig/.webproyectsaccess $username > /dev/null 2>&1
    respuestasalir
    echo "paso 3: webacc1 $username $existe y errores: $errore" >> $archivolog
    htpasswd -D $archivosconfig/.webarchivosaccess $username > /dev/null 2>&1
    respuestasalir
    echo "paso 3: webacc2 $username $existe y errores: $errore" >> $archivolog
    htpasswd -D $archivosconfig/.webreportesaccess $username > /dev/null 2>&1
    respuestasalir
    echo "paso 3: webacc3 $username $existe y errores: $errore" >> $archivolog
    echo "paso 3: eliminado $username de webdav $existe y errores: $errore"
    ejabberdctl --auth $adminusersys $dominio $adminusercla  check_account $username $dominio 2>/dev/null
    existe=$?
    if [ "$existe" == "0" ]; then
        ejabberdctl --auth $adminusersys $dominio $adminusercla unregister $username $dominio
    fi
    respuestasalir
    echo "paso 4: eliminado $username de chat $existe y errores: $errore" >> $archivolog
    echo "paso 4: eliminado $username de chat $existe y errores: $errore"
    # agregar al grupo asignado,verifica si existe en grupo
    existe=$(ejabberdctl --auth $adminusersys $dominio $adminusercla  srg_get_members $grupo $dominio | grep $username);
    if [ -n "$existe"  ]; then
        ejabberdctl --auth $adminusersys $dominio $adminusercla  srg_user_del $username $dominio $grupo $dominio
    fi
    respuestasalir
    echo "paso 4: eliminado $username de chatgroup $grupo $existe y errores: $errore" >> $archivolog
    echo "paso 4: eliminado $username de chatgroup $grupo $existe y errores: $errore"
    #eliminar de usuarios de tickets pero no de staff aun, de alli hacer manualmente
    mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "SET SQL_SAFE_UPDATES=0;DELETE FROM simpleticket.ost_user_account WHERE username='$username';DELETE FROM simpleticket.ost_user WHERE name='$username';DELETE FROM simpleticket.ost_user_email WHERE address='$usercorreo';SET SQL_SAFE_UPDATES=1;" $elsoportedb
#    mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "SET SQL_SAFE_UPDATES=0;DELETE FROM simpleticket.ost_staff WHERE username='$username';SET SQL_SAFE_UPDATES=1;" $elsoportedb
    respuestasalir
    echo "paso 5: eliminado $username de elsoporte sal $? y errores: $errore" >> $archivolog
    echo "paso 5: eliminado $username de elsoporte sal $? y errores: $errore"
    # added to owncloud instance, no cares if are in another host (requires the patched restapi from simplemail)
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users -s -d usernid="$username" -d passwordn="$claveuse" --user "$adminusersys:$adminusercla" -XPOST -k >/dev/null
    respuestasalir
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d email="$username@$dominio" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
    respuestasalir
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d password=$claveuse --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
    respuestasalir
    echo "paso 6: eliminado $username de elfichero sal $? y errores: $errore" >> $archivolog
    echo "paso 6: eliminado $username de elfichero sal $? y errores: $errore"
    
}

temporalosticket()
{
	existe2=$(mysql -u simpleticket -psimpleticket.1.com.net -h "$elsoporteh" -e "SELECT * FROM simpleticket.ost_user WHERE name='$username'" simpleticket)
        if [ -z "$existe2"  ]; then
		mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "INSERT INTO ost_user (org_id,default_email_id,status,name,created,updated)VALUES (1,0,0,'$username',CURDATE(),CURDATE());" $elsoportedb >/dev/null 2>&1
	fi
	existe2=""
	existe2=$(mysql -u simpleticket -psimpleticket.1.com.net -h "$elsoporteh" -e "SELECT * FROM simpleticket.ost_user_account WHERE username='$username'" simpleticket)
        if [ -z "$existe2"  ]; then
    		mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "INSERT INTO ost_user_account (user_id,status,timezone_id,lang,username,passwd,backend,registered) VALUES ( (SELECT id FROM ost_user WHERE name = '$username'),9,9,NULL,'$username',md5('$claveuse'),NULL,CURDATE());" $elsoportedb >/dev/null 2>&1
    	fi
    	existe2=""
	existe2=$(mysql -u simpleticket -psimpleticket.1.com.net -h "$elsoporteh" -e "SELECT * FROM simpleticket.ost_user_email WHERE address='$username@intranet1.net.ve'" simpleticket)
        if [ -z "$existe2"  ]; then
    		mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "INSERT INTO ost_user_email (user_id,address) VALUES( (SELECT id FROM ost_user WHERE name = '$username'),'$username@intranet1.net.ve');" $elsoportedb >/dev/null 2>&1
	fi
	mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "SET SQL_SAFE_UPDATES=0;UPDATE ost_user SET default_email_id = (SELECT id FROM ost_user_email WHERE address = '$username@intranet1.net.ve') WHERE id = (SELECT user_id FROM ost_user_email WHERE address = '$username@intranet1.net.ve');SET SQL_SAFE_UPDATES=1;" $elsoportedb >/dev/null 2>&1
}


cambiarclave()
{
    temporalosticket
    	# 3) cambio de clave en el nuevo osticket simpleticket
	mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "UPDATE ost_staff SET passwd = md5('$claveuse') WHERE username = '$username';" $elsoportedb  >/dev/null 2>&1
	mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "UPDATE ost_user_account SET passwd = md5('$claveuse') WHERE username = '$username';" $elsoportedb  >/dev/null 2>&1
	respuestasalir
	mysql -u $elsoporteu -p$elsoportec -h $elsoporteh -e "UPDATE ost_user_account SET passwd = md5('$claveuse') WHERE username = '$username';" $elsoportedb  >/dev/null 2>&1
	# 4 integracion con redmine el gestor de proyectos del departamento
	mysql -u $elproyectou -p$elproyectoc -h $elproyectoh -e "UPDATE users SET hashed_password=sha1(sha1('$claveuse')), salt='' WHERE login='$username';" $elproyectodb >/dev/null 2>&1
        # extra integracion con systema gastos:
	mysql -u $elproyectoug -p$elproyectocg -h $elproyectohg -e "SET SQL_SAFE_UPDATES=0;UPDATE gastossystema.usuarios SET clave='$claveuse', sessionficha='intranet' WHERE intranet='$username';" $elproyectodbg >/dev/null 2>&1

    touch $archivosconfig/.webproyectsaccess;touch $archivosconfig/.webarchivosaccess; touch $archivosconfig/.webreportesaccess
	# 5) accesos web dav claves para el permiso via wervidor web (basic authz)
        htpasswd -b $archivosconfig/.webproyectsaccess $username $claveuse > /dev/null 2>&1;sleep 1
	respuestasalir
	if [ -z  $(groups $username | grep &>/dev/null "stema")  ]; then
	    htpasswd -b $archivosconfig/.webarchivosaccess $username $claveuse > /dev/null 2>&1;sleep 1
	fi # solo entra si pertenece al grupo, solo permite usuarios de grupos administrativos
	if [ -z  $(groups $username | grep &>/dev/null '\wystema\w\|gasto\|\wresidenci\w\|\wlcaldi\w\|\wmpuest\w\|\woordina\w\|\wperacio\w\|\wesoreri\w\|\wuditori\w') ]; then
	    htpasswd -b $archivosconfig/.wereportesaccess $username $claveuse > /dev/null 2>&1
	fi
    curl http://$adminusersys:$adminusercla@$dominio2/elfichero/ocs/v1.php/cloud/users -s -d usernid="$username" -d passwordn="$claveuse" --user "$adminusersys:$adminusercla" -XPOST -k >/dev/null 2>&1; sleep 1
    curl http://$adminusersys:$adminusercla@$dominio2/elfichero/ocs/v1.php/cloud/users/$username -s -d email="$username@$dominio" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1;sleep 1
    curl http://$adminusersys:$adminusercla@$dominio2/elfichero/ocs/v1.php/cloud/users/$username -s -d password=$claveuse --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
	# 6) added to owncloud instance, no cares if are in another host (requires the patched restapi from simplemail)
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users -s -d usernid="$username" -d passwordn="$claveuse" --user "$adminusersys:$adminusercla" -XPOST -k >/dev/null 2>&1; sleep 1
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d email="$username@$dominio" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1;sleep 1
    curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d password=$claveuse --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
	respuestasalir
	# 7) cambiar clave al usuario en el sistema operativo
	echo $username:$claveuse | /usr/sbin/chpasswd;
	respuestasalir
	# 8) cambiar clave en el sistema de mensajeria (local instalado en misma maquina)
	/usr/sbin/ejabberdctl register $username $dominio $claveuse > /dev/null 2>&1
	/usr/sbin/ejabberdctl change_password $username $dominio $claveuse > /dev/null 2>&1
	respuestasalir
	# 3) cambiar clave en el owncloud para simple net, asignar el correo y asegurar la clave
	curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d email="$username@$dominio" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
	 respuestasalir
	curl http://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -s -d password="$claveuse" --user $adminusersys:$adminusercla -XPUT -k >/dev/null 2>&1
	 respuestasalir
	if [ "$comando" == "clave" -a "$claveuse" != "" ]; then
	exit $errore
	fi
}

aumentarquotamail()
{
        maildirmake -q $quota$sufix /home/$username/Maildir/;respuestasalir;
        chown $username:$username /home/$username/Maildir/maildirsize;respuestasalir
}

aumentarquotanube()
{
        curl https://$adminusersys:$adminusercla@$dominio/elfichero/ocs/v1.php/cloud/users/$username -XPUT -k -d quota=$quota -d userid=$username >/dev/null 2>&1
        respuestasalir
}

if [ "$comando" == "reseteo" ]; then
    if [ -d /home/$username ]; then
        existe=$(grep -E -i -w "$username" /etc/passwd)
        if [ "x$existe" != "x" ]; then
    	    errore=2
    	    messageerror="error: usuario no existe, o es invalido..\n usuario $username, con orden $comando,\n en nodo del dominio $dominio"
        else
            echo "Sobreesritura en creacion usuario usuario existe usuario que ejecuta es $username la que puso fue $claveuse"
                procesar_usuario
            echo "USUARIO EXISTE!!!!!!!!!!!!!!!!!!!!!!!! reseteado $username"
    	fi
    else
        errore=2
        echo "no puede reiniciar usuario si no existe, revise el nombre que indico! error 1"
    fi
        echo -e "Procesado usuario $username, con orden $comando,\n en nodo del dominio $dominio .\nResultados errores: $errore ocurridos, usuario victima es $username, quien ejecuta es $adminusersys" | mail -s "sysnet: $comando usuario $username: $errore" postmaster
	exit $errore
else
    if [ "$comando" == "crear" -a "x$claveuse" != "x" ]; then
		if [ -d /home/$username ]; then
			echo "el usuario ya esta creado, sus datos son privados y se preservaran";errore=1
		else
			procesar_usuario
		fi
    elif [ "$comando" == "borrar" ]; then
        destruir_usuario
    elif [ "$comando" == "clave" -a "x$claveuse" != "x" ]; then
        existe=$(grep -E -i -w "$username" /etc/passwd)
        if [ "x$existe" != "x" ]; then
            cambiarclave
            exit $errore
        else
            errore=$?
            echo "paso error clave: $username sal $errore;existe? con clave $claveuse y errores: $errore"
    	    errore=2
    	    messageerror="error: usuario no existe, o es invalido..\n usuario $username, con orden $comando,\n en nodo del dominio $dominio"
    	    exit $errore
    	fi
    elif [ "$comando" == "desactivar" ]; then
        claveuse=systemas.com
        echo "desactivando $username de los sistemas"
        cambiarclave
    elif [ "$comando" == "tamanioe" ]; then
        aumentarquotamail
    elif [ "$comando" == "tamaniof" ]; then
        aumentarquotanube
    elif [ "$comando" == "tamanio" ]; then
        aumentarquotanube
        aumentarquotamail
    else
        ayuda
        errore=9
        messageerror="error: comando o parametro faltante/invalido..\n usuario $username, con orden $comando,\n en nodo del dominio $dominio"
    fi
	if [ "$errore" -gt "1" ]; then
            echo -e $messageerror
    	    echo -e $messageerror | mail -s "Error sysnet $comando $username: $errore" postmaster
        else    
            echo -e "Procesado usuario $username, con orden $comando,\n en nodo del dominio $dominio .\nResultados errores: $errore ocurridos, usuario victima es $username, quien ejecuta es $adminusersys" | mail -s "sysnet: $comando usuario $username: $errore" postmaster
        fi
    exit $errore
fi

ayuda

