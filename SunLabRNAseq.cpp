// SunLabRNAseq.cpp : This file contains the 'main' function. Program execution begins and ends there.
//

#include <iostream>
#include <stdlib.h>     /* srand, rand */
#include <time.h>       /* time */
#include <string> 
#include <fstream>

int main()
{
    std::ifstream MyReadFile("programFiles.txt");
    std::string line;
    std::getline(MyReadFile, line);

    srand(time(NULL));
    int portN = rand() % 8000 + 1;
    std::string port = std::to_string(portN);
    if (portN < 1000) port = "0" + port;
    if (portN < 100) port = "0" + port;
    if (portN < 10) port = "0" + port;
    std::string portCommandS = "cd " + line + " && start /b php -S 127.0.0.1:" + port;

    char* portCommand = new char[portCommandS.size() + 1];
    std::copy(portCommandS.begin(), portCommandS.end(), portCommand);
    portCommand[portCommandS.size()] = '\0';
    
    std::cout << portCommand << std::endl;
    system(portCommand);

    std::string application = "start chrome --app=http://127.0.0.1:" + port;
    char* app = new char[application.size() + 1];
    std::copy(application.begin(), application.end(), app);
    app[application.size()] = '\0';


    system(app);

    delete[] portCommand;
    delete[] app;
}

