# Norimos Bridge

## Requirements

- Paradigma SystaComfort Controller Generation 1 ArticleNo. 09-7301 (only works in combination with LON Module
  ArticleNo. 09-7325)

## Installation

TBD

## Configuration

TBD

```bash
bash> stty -a -F /dev/ttyS0
```

**Expected Output**

```bash
speed 19200 baud; rows 0; columns 0; line = 0;
intr = ^C; quit = ^\; erase = ^?; kill = ^U; eof = ^D; eol = <undef>; eol2 = <undef>; swtch = <undef>; start = ^Q; stop = ^S; susp = ^Z; rprnt = ^R; werase = ^W; lnext = ^V; discard = ^O;
min = 1; time = 0;
-parenb -parodd -cmspar cs8 hupcl -cstopb cread clocal -crtscts
-ignbrk -brkint -ignpar -parmrk -inpck -istrip -inlcr -igncr -icrnl -ixon -ixoff -iuclc -ixany -imaxbel -iutf8
-opost -olcuc -ocrnl -onlcr -onocr -onlret -ofill -ofdel nl0 cr0 tab0 bs0 vt0 ff0
-isig -icanon iexten -echo -echoe echok -echonl -noflsh -xcase -tostop -echoprt echoctl echoke -flusho -extproc
```