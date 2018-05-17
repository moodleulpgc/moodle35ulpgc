var dtlabels;
var dslabels;
var brlabels;

function createCharts(datas){
    Chart.defaults.global.responsive = true;

    dtlabels = getTypeLabels(datas);
    var dtdatas = getTypeDatas(datas);
    var dtcolors = getColorsNum(dtdatas);

    var devicetypecanvas = $("#chart_devicetypes");
    var devicetypechart = new Chart(devicetypecanvas, {
        type: 'pie',
        data: {
            labels: dtlabels,
            datasets: [{
                data: dtdatas,
                backgroundColor: dtcolors
            }]
        },
    });

    dslabels = getSystemLabels(datas);
    var dsdatas = getSystemDatas(datas);
    var dscolors = getColorsNum(dslabels);

    var devicesystemcanvas = $("#chart_devicesystems");
    var devicesystemchart = new Chart(devicesystemcanvas, {
        type: 'bar',
        data: {
            labels: dslabels,
            datasets: [{
                label: "Percent",
                data: dsdatas,
                backgroundColor: dscolors
            }]
        },options: {
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });

    brlabels = getBrowserLabels(datas);
    var brdatas = getBrowserDatas(datas);
    var brcolors = getColorsNum(brlabels);

    var devicebrowsercanvas = $("#chart_devicebrowsers");
    var devicebrowserchart = new Chart(devicebrowsercanvas, {
        type: 'pie',
        data: {
            labels: brlabels,
            datasets: [{
                data: brdatas,
                backgroundColor: brcolors
            }]
        },
    });

    var filtereddatas = datas;
    for(var fkey in filtereddatas){
        if(filtereddatas[fkey].devicedisplaysizex == null){
            delete filtereddatas[fkey];
        }
    }
    var displaydata = getDisplayDatasets(filtereddatas);
    console.log(displaydata);
    var devicescreencanvas = $("#chart_devicedisplaysize");
    window.myScatter = Chart.Scatter(devicescreencanvas, {
        data: displaydata,
        options: {
            title: {
                display: false,
            },
            scales: {
                xAxes: [{
                    position: 'top',
                    gridLines: {
                        zeroLineColor: "rgba(0,255,0,1)"
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'width in px'
                    }
                }],
                yAxes: [{
                    position: 'left',
                    gridLines: {
                        zeroLineColor: "rgba(0,255,0,1)"
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'height in px'
                    }
                }]
            },
            showLines: false,
        }
    });

    var filteredwindowdatas = datas;
    for(var fkey in filteredwindowdatas){
        if(filteredwindowdatas[fkey].devicewindowsizex == null){
            delete filteredwindowdatas[fkey];
        }
    }
    var windowdata = getWindowDatasets(filteredwindowdatas);
    var devicewindowcanvas = $("#chart_devicewindowsize");
    window.myScatter = Chart.Scatter(devicewindowcanvas, {
        data: windowdata,
        options: {
            title: {
                display: false,
            },
            scales: {
                xAxes: [{
                    position: 'top',
                    gridLines: {
                        zeroLineColor: "rgba(0,255,0,1)"
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'width in px'
                    }
                }],
                yAxes: [{
                    position: 'left',
                    gridLines: {
                        zeroLineColor: "rgba(0,255,0,1)"
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'height in px'
                    }
                }]
            },
            showLines: false,
        }
    });
}

function getDisplayDatasets(datas){
    var databuffer = [];
    for(ddata in datas){
        var point = {device: datas[ddata].devicetype, x: datas[ddata].devicedisplaysizex, y: datas[ddata].devicedisplaysizey};
        databuffer.push(point);
    }
    return convertTChartData(databuffer);
}

function getWindowDatasets(datas){
    var databuffer = [];
    for(ddata in datas){
        var point = {device: datas[ddata].devicetype, x: datas[ddata].devicewindowsizex, y: datas[ddata].devicewindowsizey};
        databuffer.push(point);
    }
    return convertTChartData(databuffer);
}

function convertTChartData(datacon){
    var chartdata = new Object();
    chartdata.datasets = new Array();
    for(var ll = 0; ll < datacon.length; ll++){
        var look;
        if((look = checkifexistsinArray(chartdata.datasets, datacon[ll].device)) !== false){
            var inObject = new Object();
            inObject.x = datacon[ll].x;
            inObject.y = datacon[ll].y;
            look.push(inObject);
        }else{
            var setObject = new Object();
            setObject.label = datacon[ll].device;
            setObject.data = new Array();
            var obColor = getColorByValue(chartdata.datasets);
            setObject.backgroundColor = obColor;
            setObject.pointBorderColor = obColor;
            setObject.pointBackgroundColor = obColor;
            var inObject = new Object();
            inObject.x = datacon[ll].x;
            inObject.y = datacon[ll].y;
            setObject.data.push(inObject);
            chartdata.datasets.push(setObject);
        }
    }

    return chartdata;
}

function resizetocount(val, max){
    if(val >= max){
        return max;
    } else {
        return val;
    }
}

function checkifexistsinArray(set, look){
    for(var i = 0; i < set.length; i++) {
        if (set[i].label == look) {
            return set[i].data;
        }
    }
    return false;
}

function getGround(datas){
    var groundval = 0;
    for (var i in datas) {
        groundval += datas[i];
    }
    return groundval;
}

function getPercent(ground, data){
    var per = (data / ground) * 100;
    return Math.round (per * 100) / 100;
}

function getTypeLabels(datas){
    var td = [];
    $.each(datas, function(index, value) {
        if($.inArray(value.devicetype, td) === -1) {
            td.push(value.devicetype);
        }
    });
    return td;
}

function getSystemLabels(datas){
    var ts = [];
    $.each(datas, function(index, value) {
        if($.inArray(value.devicesystem, ts) === -1) {
            ts.push(value.devicesystem);
        }
    });
    return ts;
}

function getBrowserLabels(datas){
    var db = [];
    $.each(datas, function(index, value) {
        if($.inArray(value.devicebrowser, db) === -1) {
            db.push(value.devicebrowser);
        }
    });
    return db;
}

function getTypeDatas(datas){
    var dtdatas = [];
    $.each(datas, function(index, value) {
        if(typeof dtdatas[value.devicetype] === 'undefined') {
            dtdatas[value.devicetype] = 1;
        }else{
            dtdatas[value.devicetype] += 1;
        }
    });
    var groundval = getGround(dtdatas);
    var dtwrap = [];
    for (var i in dtdatas) {
        dtwrap.push(getPercent(groundval,dtdatas[i]));
    }
    return dtwrap;
}

function getSystemDatas(datas){
    var dtdatas = [];
    $.each(datas, function(index, value) {
        if(typeof dtdatas[value.devicesystem] === 'undefined') {
            dtdatas[value.devicesystem] = 1;
        }else{
            dtdatas[value.devicesystem] += 1;
        }
    });
    var groundval = getGround(dtdatas);
    var dtwrap = [];
    for (var i in dtdatas) {
        dtwrap.push(getPercent(groundval,dtdatas[i]));
    }
    return dtwrap;
}

function getBrowserDatas(datas){
    var dtdatas = [];
    $.each(datas, function(index, value) {
        if(typeof dtdatas[value.devicebrowser] === 'undefined') {
            dtdatas[value.devicebrowser] = 1;
        }else{
            dtdatas[value.devicebrowser] += 1;
        }
    });
    var groundval = getGround(dtdatas);
    var dtwrap = [];
    for (var i in dtdatas) {
        dtwrap.push(getPercent(groundval,dtdatas[i]));
    }
    return dtwrap;
}

function getRandomColors(num){
    var colors = [];
    for (i = 0; i < num; i++) {
        colors.push(getRandomColor());
    }
    return colors;
}

function getRandomColor() {
    return '#' + Math.floor(Math.random() * 16777215).toString(16);
}

function getColorsNum(cdatas){
    var colors = [];
    for (i = 0; i < cdatas.length; i++) {
        colors.push(getColorByValue(colors));
    }
    return colors;
}

function getColorByValue(list){
    var collist = ['#ce4d45', '#f19c65', '#ffd265', '#2aa876', '#0a7b83', '#588c7e', '#f2e394', '#f2ae72', '#d96459', '#8c4646'];
    if(list.length >= collist.length) {
        return getRandomColor();
    }
    return collist[list.length];
}