DROP TABLE git_commits;
CREATE TABLE git_commits(author TEXT,timestamp INTEGER,gitid TEXT,msg TEXT, prefix TEXT, PRIMARY KEY(prefix,timestamp));


