import React from "react";
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
    Cell,
} from "recharts";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { useLanguage } from "@/Contexts/LanguageContext";

const ResourceDistributionChart = ({ data, currentTerm, currentResource }) => {
    const { t } = useLanguage();

    // Prepare chart data - include current resource if not already in data
    let chartData = [];
    if (data && data.length > 0) {
        chartData = data.slice(0, 10).map((item) => ({
            name:
                item.resource_name.length > 20
                    ? `${item.resource_name.substring(0, 20)}...`
                    : item.resource_name,
            fullName: item.resource_name,
            count: item.count,
            resourceId: item.resource_id,
            isCurrentResource: false,
        }));
    }

    // If no data or empty data, create data with just current resource
    if (!data || data.length === 0) {
        if (currentResource) {
            chartData = [
                {
                    name:
                        currentResource.name.length > 20
                            ? `${currentResource.name.substring(0, 20)}...`
                            : currentResource.name,
                    fullName: currentResource.name,
                    count: 1,
                    resourceId: currentResource.id,
                    isCurrentResource: true,
                },
            ];
        } else {
            return (
                <Card className="w-full">
                    <CardHeader>
                        <CardTitle className="text-sm sm:text-base">
                            {t("charts.resource_distribution")}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-center p-4 text-gray-500 dark:text-gray-400">
                            <p className="text-sm">
                                {t("charts.no_similar_terms_found")}
                            </p>
                            <p className="text-xs mt-2">
                                {t("charts.only_showing_current_resource")}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            );
        }
    }

    // Check if current resource is in the data, if not add it
    if (
        currentResource &&
        !chartData.some((item) => item.resourceId === currentResource.id)
    ) {
        chartData.unshift({
            name:
                currentResource.name.length > 20
                    ? `${currentResource.name.substring(0, 20)}...`
                    : currentResource.name,
            fullName: currentResource.name,
            count: 1,
            resourceId: currentResource.id,
            isCurrentResource: true,
        });

        // Limit to 10 items after adding current resource
        if (chartData.length > 10) {
            chartData = chartData.slice(0, 10);
        }
    }

    // Colors for the bars
    const COLORS = [
        "#0088FE",
        "#00C49F",
        "#FFBB28",
        "#FF8042",
        "#8884D8",
        "#82CA9D",
        "#FF6B6B",
        "#4ECDC4",
        "#FFA726",
        "#66BB6A",
    ];

    // Custom tooltip
    const CustomTooltip = ({ active, payload, label }) => {
        if (active && payload && payload.length) {
            const dataItem = payload[0].payload;
            return (
                <div className="bg-white dark:bg-gray-800 p-3 border border-gray-200 dark:border-gray-700 rounded shadow-sm">
                    <p className="font-medium text-gray-900 dark:text-white">
                        {dataItem.fullName}
                    </p>
                    <p className="text-sm text-gray-600 dark:text-gray-300">
                        {t("charts.term_occurrences")}:{" "}
                        <span className="font-semibold">{dataItem.count}</span>
                    </p>
                    {dataItem.isCurrentResource && (
                        <p className="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                            {t("charts.current_resource")}
                        </p>
                    )}
                    {currentTerm && (
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {t("charts.current_term")}:{" "}
                            {currentTerm.term_en || currentTerm.term_ar}
                        </p>
                    )}
                </div>
            );
        }
        return null;
    };

    return (
        <Card className="w-full">
            <CardHeader>
                <CardTitle className="text-sm sm:text-base">
                    {t("charts.resource_distribution")}
                </CardTitle>
                <p className="text-xs text-gray-500 dark:text-gray-400">
                    {t("charts.distribution_description")}
                </p>
            </CardHeader>
            <CardContent>
                <div className="h-64 sm:h-72 md:h-80">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart
                            data={chartData}
                            margin={{
                                top: 5,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}
                        >
                            <CartesianGrid
                                strokeDasharray="3 3"
                                stroke="#e5e7eb"
                            />
                            <XAxis
                                dataKey="name"
                                angle={-45}
                                textAnchor="end"
                                height={60}
                                tick={{ fontSize: 12 }}
                                stroke="#6b7280"
                            />
                            <YAxis
                                stroke="#6b7280"
                                tick={{ fontSize: 12 }}
                                label={{
                                    value: t("charts.occurrences"),
                                    angle: -90,
                                    position: "insideLeft",
                                    offset: -10,
                                    style: {
                                        textAnchor: "middle",
                                        fontSize: 12,
                                    },
                                }}
                            />
                            <Tooltip content={<CustomTooltip />} />
                            <Legend
                                verticalAlign="top"
                                height={36}
                                formatter={() => t("charts.term_count")}
                            />
                            <Bar
                                dataKey="count"
                                name={t("charts.occurrences")}
                                radius={[4, 4, 0, 0]}
                            >
                                {chartData.map((entry, index) => (
                                    <Cell
                                        key={`cell-${index}`}
                                        fill={
                                            entry.isCurrentResource
                                                ? "#3B82F6"
                                                : COLORS[index % COLORS.length]
                                        }
                                        stroke={
                                            entry.isCurrentResource
                                                ? "#1D4ED8"
                                                : undefined
                                        }
                                        strokeWidth={
                                            entry.isCurrentResource ? 1 : 0
                                        }
                                    />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                </div>

                {/* Summary statistics */}
                <div className="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.total_resources")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {chartData.length}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.total_occurrences")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white">
                            {chartData.reduce(
                                (sum, item) => sum + item.count,
                                0,
                            )}
                        </p>
                    </div>
                    <div className="bg-gray-50 dark:bg-gray-800 p-3 rounded">
                        <p className="text-gray-500 dark:text-gray-400">
                            {t("charts.top_resource")}
                        </p>
                        <p className="text-lg font-semibold text-gray-900 dark:text-white truncate">
                            {chartData[0]?.fullName || "-"}
                        </p>
                    </div>
                </div>

                {/* Resource list */}
                <div className="mt-4">
                    <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {t("charts.resource_details")}
                    </h4>
                    <div className="max-h-40 overflow-y-auto">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t("charts.resource")}
                                    </th>
                                    <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t("charts.occurrences")}
                                    </th>
                                    <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        {t("charts.percentage")}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                                {chartData.slice(0, 5).map((item, index) => {
                                    const totalOccurrences = chartData.reduce(
                                        (sum, i) => sum + i.count,
                                        0,
                                    );
                                    const percentage =
                                        totalOccurrences > 0
                                            ? (
                                                  (item.count /
                                                      totalOccurrences) *
                                                  100
                                              ).toFixed(1)
                                            : 0;

                                    return (
                                        <tr
                                            key={item.resourceId}
                                            className={`hover:bg-gray-50 dark:hover:bg-gray-800 ${item.isCurrentResource ? "bg-blue-50 dark:bg-blue-900/20" : ""}`}
                                        >
                                            <td className="px-3 py-2 text-xs text-gray-900 dark:text-white truncate max-w-[120px]">
                                                {item.fullName}
                                                {item.isCurrentResource && (
                                                    <span className="ml-1 text-xs text-blue-600 dark:text-blue-400 font-medium">
                                                        ({t("charts.current")})
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                                {item.count}
                                            </td>
                                            <td className="px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                                {percentage}%
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
};

export default ResourceDistributionChart;
