# README - To install the project #

### Install & configure the project "UCM"

**1. Clone the project**

```
git clone https://<USERNAME>@bitbucket.org/onja/ucm-repo.git
```

**2. Copy-paste the file content ".env.dist" of project in the file ".env" and change this section with your database configuration**

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
```
**3. Run**
```
> composer install
```

**4. Import SQL dump from directory: sql/ucm.sql OR execute migration with:**
```
php bin/console doctrine:migrations:migrate
```

**4. Admin access**
```
> url: /admin
> login: admin@ucm.mg
> mdp: Pa$$word!
```

**5. Import student**
```
> template file location: /templates/import/L1DROIT.csv
> file fields list: 
   - Matricule
   - Mot de passe
   - last_name
   - first_name
   - mention
   - niveau
   - diminutif
   - parcours
   - Nationality
   - civilite
   - Lieu de naissance
   - cin num
   - address       
   - phone
   - Religion
   - email
> import command: php bin/console app:student:import
```
** 18/09/2022
** Adding "gumlet/php-image-resize": "2.0.*" 
enjoy :)